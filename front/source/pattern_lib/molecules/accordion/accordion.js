class Accordion {

  constructor(element){

    // Store references to DOM elements
    this.element = element;

    // Store class properties
    this.$toggleButton = $('.js-toggle-question', this.element);

    this.$toggleButton.on('click', (e) => {
      this.toggleAnswer(e.target);
    });

  }

  toggleAnswer(elem) {

    var $elem = $(elem).closest('.m-accordion__item').children('.js-toggle-answer');
    var maxHeight =  $elem.css('max-height');

    if ( maxHeight !== "0px" ){
      $elem.removeClass("m-accordion__answer--open");
      $elem.css('max-height','0');
    } else {
      $elem.delay(500).addClass("m-accordion__answer--open");
      var scrollHeight = $elem.prop('scrollHeight') + "px";
      $elem.css('max-height', scrollHeight);
    }
  }

}

module.exports = Accordion;

