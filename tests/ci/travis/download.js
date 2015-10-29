var system = require('system');
var env = system.env;

function loginIBM() {
    system.stdout.writeLine('Logging');

    page.onLoadFinished = downloadPage;
    page.evaluate(function () {
        document.getElementById('firstName').value = 'test';
        document.getElementById('lastName').value = 'travis';
        document.getElementById('emailAddress').value = 'travis@test.tst';
        document.getElementById('company').value = 'test for travis';
        document.getElementById('countryResidence').value = 'US';

        document.getElementById('NC_CHECK_EMAIL').checked = false;
        document.getElementById('NC_CHECK_PHONE').checked = false;
        document.getElementById('NC_CHECK_POSTAL').checked = false;

        document.getElementById('licenseAccepted').checked = true;

        document.forms[1].submit();
    });
}

function downloadPage() {
    system.stdout.writeLine('Going to download http page');
    page.onLoadFinished = downloadLink;
//    page.evaluate(function () {
//        var tab = document.getElementsByClassName('ibm-last-tab')[0];
//        tab.childNodes[0].click();
//    });
    page.open('https://www-01.ibm.com/marketing/iwm/iwm/web/download.do?source=swg-db2expressc&S_PKG=dllinux64&S_TACT=100KG28W&lang=en_US&&&dlmethod=http');

}

function downloadLink() {
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

page.onResourceError = function (error) {
//    system.stderr.writeLine(JSON.stringify(error));
//    phantom.exit(1);
}
page.onLoadFinished = loginIBM;
page.open('https://www-01.ibm.com/marketing/iwm/iwm/web/pick.do?source=swg-db2expressc&S_PKG=dllinux64&S_TACT=100KG28W&lang=en_US&dlmethod=http');