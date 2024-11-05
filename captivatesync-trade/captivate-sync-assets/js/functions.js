var cfm_timer;

function cfmsync_toaster(result, message, duration=5) {

	clearTimeout(cfm_timer);

	var cfmsync_toaster = document.querySelector('.cfm-toaster');

	if (result == 'success') {
		cfmsync_toaster.className = "cfm-toaster cfm-is-visible cfm-toast-success";
	}
	else if (result == 'warning') {
		cfmsync_toaster.className = "cfm-toaster cfm-is-visible cfm-toast-warning";
	}
	else if (result == 'info') {
		cfmsync_toaster.className = "cfm-toaster cfm-is-visible cfm-toast-info";
	}
	else {
		cfmsync_toaster.className = "cfm-toaster cfm-is-visible cfm-toast-error";
	}

	document.querySelector('.cfm-toaster-text').innerHTML = message;

	cfm_timer = setTimeout(function() {
		cfmsync_toaster.classList.remove("cfm-is-visible");
	}, duration*1000);
}

document.querySelector(".cfm-toast-dismiss").addEventListener('click', function() {
	document.querySelector('.cfm-toaster').classList.remove("cfm-is-visible");
}, false);

function cfm_get_url_vars() {
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,
	function(m,key,value) {
	 	vars[key] = value;
	});
	return vars;
}

function cfm_truncate(str, n){
  return (str.length > n) ? str.substr(0, n-1) + '&hellip;' : str;
}

function cfm_convert_to_slug(text) {
	return text
	.toLowerCase()
	.replace( / /g,'-' )
	.replace( /-{2,}/g, '-' )
	.replace( /[^\w-]+/g,'' );
}

function cfm_milliseconds_to_str(milliseconds) {
    function numberEnding (number) {
        return (number > 1) ? 's' : '';
    }

    var temp = Math.floor(milliseconds / 1000);
    var years = Math.floor(temp / 31536000);
	var days = Math.floor((temp %= 31536000) / 86400);
	var hours = Math.floor((temp %= 86400) / 3600);
	var minutes = Math.floor((temp %= 3600) / 60);
	var seconds = temp % 60;
	//TODO: Months, Weeks, etc

    if (years) {
        return years + ' year' + numberEnding(years) + ', ' + days + ' day' + numberEnding(days) + ', ' + hours + ' hour' + numberEnding(hours) + ', ' + minutes + ' minute' + numberEnding(minutes) + ', ' + seconds + ' second' + numberEnding(seconds);
    }
    if (days) {
        return days + ' day' + numberEnding(days) + ', ' + hours + ' hour' + numberEnding(hours) + ', ' + minutes + ' minute' + numberEnding(minutes) + ', ' + seconds + ' second' + numberEnding(seconds);
    }
    if (hours) {
        return hours + ' hour' + numberEnding(hours) + ', ' + minutes + ' minute' + numberEnding(minutes) + ', ' + seconds + ' second' + numberEnding(seconds);
    }
    if (minutes) {
        return minutes + ' minute' + numberEnding(minutes) + ', ' + seconds + ' second' + numberEnding(seconds);
    }
    if (seconds) {
        return seconds + ' second' + numberEnding(seconds);
    }
    return 'less than a second'; //'just now'
}

function cfm_is_datetime_future(datetime) {
	var d1 = new Date();
	var d2 = new Date(datetime);

	return (d1 > d2) ? false : true;
}

var cfm_content_spinner = '<div class="cfm-content-spinner d-flex justify-content-center align-items-center"><svg width="200px" height="200px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="lds-eclipse" style="background: none;"><path stroke="none" d="M10 50A40 40 0 0 0 90 50A40 42 0 0 1 10 50" fill="#C58FAC" transform="rotate(233.616 50 51)"><animateTransform attributeName="transform" type="rotate" calcMode="linear" values="0 50 51;360 50 51" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animateTransform></path></svg></div>';

function cfm_ucwords(str) {
    return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
        return $1.toUpperCase();
    });
}