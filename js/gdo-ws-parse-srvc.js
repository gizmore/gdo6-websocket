"use strict";
/**
 * Parse a binary gdo response.
 * Uses type service to resolve protocol.
 */
angular.module('gdo6').
service('GDOWSParseSrvc', function($q, GDOTypeSrvc) {
	
	var GDOWSParseSrvc = this;

	/**
	 * Fill a gdo by parsing a binary websocket message.
	 */
	GDOWSParseSrvc.parseBinaryGDO = function(gwsMessage, classname, gdo) {
		return GDOTypeSrvc.withType(classname).then(function(fields) {
			console.log('GDOWSParseSrvc.parseBinaryGDO()', fields);
			for (var key in fields) {
				var field = fields[key];
				if (GDOWSParseSrvc.isTypeSubmitted(field)) {
					var value = GDOWSParseSrvc.parseBinaryTypeHierarchy(gwsMessage, field);
					if (value === undefined) {
						console.error('TypeSrvc.parseBinaryType: Cannot convert '+key+' which is a '+field.type);
					} else {
						console.log("SET", key, value);
						gdo.JSON[key] = value;
					}
				}
			}
		});
	};
	
	/**
	 * Filter some gdt that are not transmitted.
	 * passwords and secrets.
	 */
	GDOWSParseSrvc.isTypeSubmitted = function(field) {
		switch (field.type) {
		case 'GDO\\User\\GDT_Password':
//		case 'GDO\\User\\GDT_Secret':
		case 'GDO\\Net\\GDT_IP':
			return false;
		}
		return true;
	};
	
	/**
	 * Parse a portion of a binary websocket message.
	 * The parsing is determined by the gdt type and its options.
	 * The type and options meta data is retrieved by Core/GetTypes and Core/GetEnums
	 * The parsing target format is the json equivalent of a response.
	 */
	GDOWSParseSrvc.parseBinaryTypeHierarchy = function(gwsMessage, field) {
		var options = field.options; // field options
		var gdtType = field.type; // field type
		var hierarc = GDOTypeSrvc.TYPES[gdtType]; // Class hierarchy
		console.log('PARSE', gdtType, options, hierarc);
		var value = GDOWSParseSrvc.parseBinaryType(gdtType, gwsMessage, gdtType, options);
		if (value === undefined) {
			for (var i in hierarc) {
				value = GDOWSParseSrvc.parseBinaryType(hierarc[i], gwsMessage, gdtType, options);
				if (value !== undefined) {
					break;
				}
			}
		}
		return value;
	};
	
	GDOWSParseSrvc.parseBinaryType = function(klass, gwsMessage, gdtType, options) {
		switch (klass) {
		case 'GDO\\Date\\GDT_Timestamp': var t = gwsMessage.read32(); return t > 0 ? new Date(t*1000).toISOString() : null;
		case 'GDO\\DB\\GDT_Decimal': return gwsMessage.readFloat();
		case 'GDO\\DB\\GDT_Int': return gwsMessage.readN(options.bytes);
		case 'GDO\\DB\\GDT_String': return gwsMessage.readString();
		case 'GDO\\DB\\GDT_Enum': var integer = gwsMessage.read8(); return integer === 0 ? null : options.enumValues[integer-1];
		}
	};
	
	return TypeSrvc;
});
