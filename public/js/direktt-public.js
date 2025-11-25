'use strict'

document.addEventListener("DOMContentLoaded", function () {
    checkForDcid()
});

function setStrictDomainSessionCookie(name, value) {

    function getDomainFromURL() {
        const domain = window.location.hostname;
        return domain.startsWith('www.') ? domain.substring(4) : domain;
    }

    const domain = getDomainFromURL();

    let cookieString = `${encodeURIComponent(name)}=${encodeURIComponent(value)}; SameSite=Strict; Path=/; Secure;`;

    cookieString += ` Domain=${domain};`;

    document.cookie = cookieString;
}

function getCookie(name) {
    const nameEQ = `${name}=`;
    const cookies = document.cookie.split(';');

    for (let i = 0; i < cookies.length; i++) {
        let cookie = cookies[i].trim();

        if (cookie.indexOf(nameEQ) === 0) {
            return cookie.substring(nameEQ.length, cookie.length);
        }
    }

    return null;
}

function getQueryStringParam(param) {

    const searchParams = new URLSearchParams(window.location.search);

    if (searchParams.has(param)) {
        return searchParams.get(param);
    } else {
        return null;
    }
}

function checkForDcid() {
    
    const dcidValue = getQueryStringParam('dcid');

    if (dcidValue !== null) {
        setStrictDomainSessionCookie('direktt_dcid', dcidValue);
    } else {
        const dcidValue = getCookie('direktt_dcid');

        if (dcidValue !== null) {
            console.log(`The value of dcid cookie is: ${dcidValue}`);
        } else {
            console.log('dcid cookie not found.');
        }
    }

}
