import alertify from 'alertifyjs';

const defaults = {
	icon: 'close-circle-o',
	type: 'error',
	message: 'Something went wrong!',
	delay: 0,
	clearAll: false,
	callback: function() {}
}

export default function ( options = {} ) {

	document.onclick = function() {
		alertify.dismissAll();
	}

	options.message = ( options.message && typeof options.message == 'string' ) ? options.message : defaults.message;
	
	let config = Object.assign({}, defaults, options);

	if ( config.clearAll ) {
		alertify.dismissAll();
	}

	switch( config.type ) {
		case 'success': {
			config.icon = 'check-circle';
			break;
		}
		case 'warning': {
			config.icon = 'alert-circle-o';
			break;
		}
		case 'info': {
			config.icon = 'info-outline';
			break;
		}
		case 'complete': {
			config.icon = 'mood';
			break;
		}
	}

	if ( config.type != 'error' ) {
		config.delay = 4;
	}

	config = Object.assign({}, config, options);

	config.icon = config.icon.trim();

	config.icon = `<i class="zmdi zmdi-${config.icon}"></i>`;
	config.message = `<span>${config.message}</span>`;
	
	return alertify.notify( config.icon + config.message, config.type, config.delay, config.callback );

}