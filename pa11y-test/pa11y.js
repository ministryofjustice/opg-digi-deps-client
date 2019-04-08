"use strict";

const pa11y = require('pa11y');

// const credentials = 'opg:monkeytrousers';
// const encodedCredentials = new Buffer(credentials).toString('base64');

const args = require('minimist')(process.argv.slice(2));

let host = args['h'];
let route = args['r'];
let user = args['e'];
let pass = args['p'];

if(!host || !route || host.indexOf('/')>-1) {
	console.log('Please enter host and route at least e.g. -h lpa-front.local -r /home');
	process.exit(0);
}

console.log('Test page: https://' + host + route);

// Test the URL
function testURL() {
	console.log('Testing specified URL...')
	pa11y('https://' + host + route, {
	    wait: 2000,
	    /*headers: {
	        Authorization: `Basic ${encodedCredentials}`
	    }*/
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
	    /*headers: {
	        Authorization: `Basic b3BnOm1vbmtleXRyb3VzZXJz`
	    },*/		
	    actions: [
	        'set field #email to ' + user,
	        'set field #password to ' + pass,
	        'click element #signin-form-submit',
	        'wait for path to not be /login?cookie=1',
	        'navigate to https://' + host + route

	    ]
	}).then((results) => {console.log(results)});
} else {
	testURL();
}

