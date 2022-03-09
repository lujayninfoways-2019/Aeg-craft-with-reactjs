class Cookie {

  constructor(element){

    // Store references to DOM elements
    this.element = element;

    // Store class properties
    this.$closeButton = $('.js-close-button', this.element);
    this.$cookieTimestamp = 365;
    this.$cookieState = "accepted";

    this.checkCookie(this.element);
  }

  setCookie(element, cname, cvalue) {
    element.style.display = 'none';

    var d = new Date();
    d.setTime(d.getTime() + (this.$cookieTimestamp * 24 * 60 * 60 * 1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
  }

  getCookie(cname) {
    var cookieName = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(cookieName) == 0) {
            return c.substring(cookieName.length, c.length);
        }
    }
    return "";
  }

  checkCookie(element) {
    var cookie = this.getCookie("cookie_notification");
    if (cookie != "") {
      element.style.display = 'none';
    } else {
      this.$closeButton.on('click', (e) => {
        this.setCookie(this.element, "cookie_notification", this.$cookieState);
      });
    }
  }
}

module.exports = Cookie;
