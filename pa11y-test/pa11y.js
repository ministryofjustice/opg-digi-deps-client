"use strict";

const pa11y = require('pa11y');

const args = require('minimist')(process.argv.slice(2));

let host = args['h'];
let route = args['r'];
let user = args['e'];
let pass = args['p'];

if(!host || !route || host.indexOf('/')>-1) {
	console.log('Please enter host and route at least e.g. -h digideps-client.local -r /report/xxx/yyy');
	process.exit(0);
}

console.log('Test page: https://' + host + route);

// Test the URL
function testURL() {
	console.log('Testing specified URL...')
	pa11y('https://' + host + route, {
	    wait: 2000,
	}).then((results) => {
	    // Do something with the results
	    console.log(results);
	});
}

// Login if username and password supplied
if(user && pass) {
	console.log('Logging in first at https://' + host + '/login');

	pa11y('https://' + host + '/login', {
		wait: 2000,

	    actions: [
	        'set field #login_email to ' + user,
	        'set field #login_password to ' + pass,
	        'click element #login_login',
	        'wait for path to not be /login',
	        'navigate to https://' + host + route

	    ]
	}).then((results) => {console.log(results)});
} else {
	testURL();
}

