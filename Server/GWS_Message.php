<?php
namespace GDO\Websocket\Server;

use GDO\Core\Logger;
use GDO\User\GDO_User;
use GDO\Core\GDOException;

final class GWS_Message
{
	private $command;
	private $from;
	private $mid = 0;
	private $data;
	private $index = 0;
	
	public function __construct($binary, $from)
	{
		$this->data = $binary;
		$this->from = $from;
	}

	public function conn() { return $this->from; }
	public function ip() { return $this->from->getRemoteAddress(); }
	public function cmd() { return $this->command; }
	/**
	 * @return GDO_User
	 */
	public function user() { return $this->from->user(); }
	public function index($index=-1) { $this->index = $index < 0 ? $this->index : $index; return $this->index; }
	public function isSync() { return $this->mid > 0; }
	
	#############
	### Reply ###
	#############
	public function replyText($command, $data='')
	{
		$payload = $this->mid > 0 ? "$command:MID:$this->mid:$data" : "$command:$data";
		Logger::logWebsocket(sprintf("%s << %s", $this->user() ? $this->user()->displayName() : '???', $payload));
		return $this->from->send($payload);
	}
	
	/**
	 * Reply to a command.
	 * Set the MessageID accordingly for synchronous messages.
	 * @param int $command 2 byte id
	 * @param string $data binary payload
	 * @return boolean
	 */
	public function replyBinary($command, $data='')
	{
		Logger::logWebsocket(sprintf("%s << BIN", $this->user() ? $this->user()->displayName() : '???'));
		$command |= $this->mid > 0 ? 0x8000 : 0; # Set LSB to mark MID reply sync msg mode.
		$payload = $this->write16($command);
		$payload.= $this->mid > 0 ? $this->write24($this->mid) : '';
		$payload.= $data;
		GWS_Message::hexdump($payload);
		return $this->from->sendBinary($payload);
	}
	
	public function replyError($code)
	{
		return $this->replyErrorMessage($code, '');
	}

	public function replyErrorMessage($code, $message)
	{
// 		$message = $message['error'];
		Logger::logWebsocket(sprintf('%s: ERROR - %s', ($this->user() ? $this->user()->displayNameLabel() : '???'), $message));
		return $this->replyBinary(0x0000, $this->write16($code).$this->writeString($message));
	}
	
	##############
	### Reader ###
	##############
	public function hasMore($n=1) { return $this->index + $n <= strlen($this->data); }
	public function readPayload() { return $this->data; }
	public function readJSON() { return json_encode($this->data); }
	public function read8($signed=true, $index=-1) { return $this->readN(1, $signed, $index); }
	public function read16($signed=true, $index=-1) { return $this->readN(2, $signed, $index); }
	public function read24($signed=true, $index=-1) { return $this->readN(3, $signed, $index); }
	public function read32($signed=true, $index=-1) { return $this->readN(4, $signed, $index); }
	public function read8u($index=-1) { return $this->readN(1, false, $index); }
	public function read16u($index=-1) { return $this->readN(2, false, $index); }
	public function read24u($index=-1) { return $this->readN(3, false, $index); }
	public function read32u($index=-1) { return $this->readN(4, false, $index); }
	public function readFloat($index=-1) { $p = unpack("f", $this->readChars(4, $index)); return array_pop($p); }
	public function readChar($index=-1) { return $this->readChars(1, $index); }
	public function readChars($num, $index=-1)
	{
		$chars = substr($this->data, $this->index($index), $num);
		$this->index += $num;
		return strrev($chars);
	}
	
	public function readN($bytes, $signed=true, $index=-1)
	{
		if (!$this->hasMore($bytes))
		{
			throw new GDOException("Buffer underflow in bytestream.");
		}
		
		$index = $this->index($index);
		$back = 0;
		for ($i = 0; $i < $bytes; $i++)
		{
			$back <<= 8;
			$back += ord($this->data[$index++]);
		}
		if ($signed)
		{
			$half = pow(2, ($bytes*8)-1);
			$back = $back > $half ? -$half+$back-$half : $back;
		}
		$this->index = $index;
		return $back;
	}
	public function readString($index=-1)
	{
		$string = '';
		$this->index($index);
		while ($char = $this->read8()) {
			$string .= chr($char);
		};
		return urldecode($string);
	}

	public function readCmd()
	{
		$cmd = $this->read16u();
		if (($cmd & 0x8000) > 0) {
			$this->mid = $this->read24();
		}
		$this->command = $cmd & 0x7FFF;
		return $this;
	}
	
	public function readTextCmd()
	{
		$firstCol = strpos($this->data, ':');
		$numParts = strpos($this->data, ':MID:') === $firstCol ? 4 : 2;
		$parts = explode(':', $this->data, $numParts);
		if ($numParts === 4)
		{
			$this->mid = $parts[2];
		}
		$this->command = $parts[0];
		$this->data = array_pop($parts);
		return $this;
	}
	
	###############
	### Factory ###
	###############
	/**
	 * Create the payload for an async message.
	 * Used only in async communication. 
	 * @param string|int $cmd
	 * @param boolean $binary
	 * @return string
	 */
	public static function payload($cmd, $binary=true)
	{
		return $binary ? self::wr16($cmd) : "$cmd:";
	}
	
	##############
	### Writer ###
	##############
	public function writeFloat($float) { return self::wrF($float); }
	public function writeDouble($double) { return self::wrD($double); }
	public function writeString($string) { return self::wrS($string); }
	public function writeTimestamp() { return self::wrTS(); }
	public function write8($value) { return self::wrN(1, $value); }
	public function write16($value) { return self::wrN(2, $value); }
	public function writeCmd($value) { return self::wrN(2, $value); }
	public function write24($value) { return self::wrN(3, $value); }
	public function write32($value) { return self::wrN(4, $value); }
	public function write64($value) { return self::wrN(8, $value); }
	public static function wr8($value) { return self::wrN(1, $value); }
	public static function wr16($value) { return self::wrN(2, $value); }
	public static function wrCmd($value) { return self::wrN(2, $value); }
	public static function wr24($value) { return self::wrN(3, $value); }
	public static function wr32($value) { return self::wrN(4, $value); }
	public static function wr64($value) { return self::wrN(8, $value); }
	public static function wrF($float) { return pack("f", floatval($float)); }
	public static function wrD($double) { return pack("d", doubleval($double)); }
	public static function wrS($string) { return urlencode($string)."\0"; }
	public static function wrTS() { return self::wr32(time()); }
	public static function wrN($bytes, $value)
	{
		$value = (int)$value;
		$write = '';
		for ($i = 0; $i < $bytes; $i++)
		{
			$write = chr($value & 0xFF).$write;
			$value >>= 8;
		}
		return $write;
	}

	###############
	### Hexdump ###
	###############
	public static function hexdump($data, $newline="\n")
	{
		static $from = '';
		static $to = '';
		
		static $width = 16; # number of bytes per line
		
		static $pad = '.'; # padding for non-visible characters
		
		if ($from==='')
		{
			for ($i=0; $i<=0xFF; $i++)
			{
				$from .= chr($i);
				$to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
			}
		}
		
		$hex = str_split(bin2hex($data), $width*2);
		$chars = str_split(strtr($data, $from, $to), $width);
		
		$offset = 0;
		foreach ($hex as $i => $line)
		{
			echo sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']' . $newline;
			$offset += $width;
		}
	}
}
