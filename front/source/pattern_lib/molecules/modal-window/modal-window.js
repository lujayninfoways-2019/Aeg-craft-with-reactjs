class ModalWindow {

  constructor(element){

    // Store references to DOM elements
    this.element = element;
    this.closeModalButtons = this.element.querySelectorAll('.modal-window__close');
    this.closeModalButtonsArray = [...this.closeModalButtons];
    this.modalInitial = this.element.querySelector('.modal-window__container.initial')
    this.modalConfirmation = this.element.querySelector('.modal-window__container.confirmation')
    this.modalInput = this.element.querySelector('.modal-window__email');
    this.modalSendEmail = this.element.querySelector('.modal-window__send-email');
    this.modalInvalid = this.element.querySelector('.modal-window__form-text--invalid.email');
    this.modalCaptchaInvalid = this.element.querySelector('.modal-window__form-text--invalid.captcha');
    this.modalSuccessButton = this.element.querySelector('.modal-window__success-button');
    this.resetModalButton = this.element.querySelector('.modal-window__success-text');
    this.ingredientsCounterField = this.element.querySelector('#mwServesField');
    this.ingredientsListField = this.element.querySelector('#mwIngredientsField');

    // Add event listeners
    this.closeModalButtonsArray.forEach(function(elem) {
      elem.addEventListener('click', () => {
        this.resetModal();
        this.closeModal();
      });
    }.bind(this));

    this.modalSuccessButton.addEventListener('click', () => {
      this.resetModal();
      this.closeModal();
    });

    this.resetModalButton.addEventListener('click', () => {
      this.resetModal();
    });

    this.modalInput.addEventListener('input', (e) => {
      this.inputHandler(e);
    });

    this.modalSendEmail.addEventListener('click', (e) => {
      e.preventDefault();
      if (this.validateEmail) {
        this.sendMail(e);
      } else {
        this.modalInvalid.style.display = 'block';
      }
    });
  }

  closeModal(){
    this.element.classList.remove('modal-window--opened');
    this.modalInvalid.style.display = 'none';
    this.modalCaptchaInvalid.style.display = 'none';
  }

  resetModal(){
    this.modalInput.value = '';
    this.ingredientsCounterField.value = '';
    this.ingredientsListField.value = '';
    this.modalSendEmail.disabled = true;
    this.modalConfirmation.style.display = 'none';
    this.modalInitial.style.display = 'block';
    this.modalInvalid.style.display = 'none';
    this.modalCaptchaInvalid.style.display = 'none';
    grecaptcha.reset();
  }

  inputHandler() {
    if (this.modalInput && this.modalInput.value) {
      this.modalSendEmail.disabled = false;
    } else {
      this.modalSendEmail.disabled = true;
    }
  }

  get validateEmail() {
    this.modalInvalid.style.display = 'none';
    var mailValid = false;
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (re.test(this.modalInput.value)) {
      mailValid = true;
    }
    return mailValid;
  }

  get captchaValid() {
    let isValid = !!grecaptcha.getResponse();
    return isValid;
  }

  sendMail() {

    if(!this.captchaValid) {
      this.modalCaptchaInvalid.style.display = 'block';
      return;
    }

    $.post(
        "/actions/aeg/email/sendIngredients",
        $(this.modalInitial).find('form').serialize()
    ).done(function() {
      $('.modal-window__container.initial').hide();
      $('.modal-window__container.confirmation').show();
    }).fail(function() {
      $('.modal-window__form-text--invalid').show();
    });
  }
}

module.exports = ModalWindow;
