
class ContactForm {

  constructor(element){

    this.element = element;
    this.contactForm = $('.m-form__container');
    this.emailInput = this.element.querySelector('.m-form__input[name="email"]');
    this.success = this.element.querySelector('.m-form__success');
    this.mailInvalid = this.element.querySelector('.m-form__invalid.email');
    this.captchaInvalid = this.element.querySelector('.m-form__invalid.captcha');
    this.mailError = this.element.querySelector('.m-form__error');

    this.contactForm.submit(function(e) {
      e.preventDefault();

      if (!this.validateEmail) {
        this.mailInvalid.style.visibility = 'visible';
      } else {
        this.mailInvalid.style.visibility = 'hidden';
      }

      if(!this.captchaValid) {
        this.captchaInvalid.style.visibility = 'visible';
        return;
      }

      this.success.style.visibility = 'hidden';
      this.mailInvalid.style.visibility = 'hidden';
      this.captchaInvalid.style.visibility = 'hidden';
      this.mailError.style.visibility = 'hidden';

      $.post(
          "/actions/aeg/email/sendQuestion",
          this.contactForm.serialize()
      ).done(function() {
        $('.m-form__success').css('visibility', 'visible');
      }).fail(function() {
        $('.m-form__error').css('visibility', 'visible');
      });
    }.bind(this));
  }

  get validateEmail() {
    var mailValid = false;
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (re.test(this.emailInput.value)) {
      mailValid = true;
    }
    return mailValid;
  }

  get captchaValid() {
    let isValid = !!grecaptcha.getResponse();
    return isValid;
  }
}

module.exports = ContactForm;
