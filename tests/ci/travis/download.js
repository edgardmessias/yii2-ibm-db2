var system = require('system');
var env = system.env;

function loginIBM(status) {
    if (status !== 'success') {
        console.log('Unable to access network');
        phantom.exit(1);
    }
    system.stdout.writeLine('Logging');

    page.onLoadFinished = downloadPage;
    page.evaluate(function () {
        document.getElementById('firstName').value = 'test';
        document.getElementById('lastName').value = 'travis';
        document.getElementById('emailAddress').value = 'travis@test.tst';
        document.getElementById('phone').value = '0000-0000';
        document.getElementById('company').value = 'test for travis';
        document.getElementById('countryResidence').value = 'US';
        document.getElementById('state').innerHTML = '<option value="AK">Alaska</option>';
        document.getElementById('state').value = 'AK';

        document.getElementById('NC_CHECK_EMAIL').checked = false;
        document.getElementById('NC_CHECK_PHONE').checked = false;
        document.getElementById('NC_CHECK_POSTAL').checked = false;

        document.getElementById('licenseAccepted').checked = true;

        document.forms[1].submit();
    });
}

function downloadPage(status) {
    if (status !== 'success') {
        console.log('Unable to access network');
        phantom.exit(1);
    }

    system.stdout.writeLine('Going to download http page');
    page.onLoadFinished = downloadLink;
    var r = page.evaluate(function () {
        var link = document.querySelector('.ibm-last-tab a');
        if (link.getAttribute('href') === '#') {
            return 'ok';
        } else {
            link.click();
        }
    });

    if (r === 'ok') {
        downloadLink('success');
    }
}

function downloadLink(status) {
    if (status !== 'success') {
        console.log('Unable to access network');
        phantom.exit(1);
    }

    system.stdout.writeLine('Picking download link');
    var link = page.evaluate(function () {
        return document.getElementsByClassName('ibm-download-link')[0].href;
    });

    if (!link) {
        system.stdout.writeLine('Link not found');
        phantom.exit(1);
        return false;
    }
    system.stdout.writeLine('Download link:');
    system.stdout.writeLine(link);
    phantom.exit(0);
}

system.stdout.writeLine('Starting');

var page = require('webpage').create();
page.settings.userAgent = 'Mozilla/5.0';

page.onConsoleMessage = function (msg) {
    system.stderr.writeLine('CONSOLE: ' + msg);
};

page.onResourceError = function (error) {
//    system.stderr.writeLine(JSON.stringify(error));
    system.stderr.writeLine(error.url + ': ' + error.errorString);
//    phantom.exit(1);
}

page.onLoadFinished = loginIBM;
page.open('https://www-01.ibm.com/marketing/iwm/iwm/web/signup.do?source=swg-db2expressc&S_TACT=100KG28W&lang=en_US&dlmethod=http&S_PKG=ov48105');
