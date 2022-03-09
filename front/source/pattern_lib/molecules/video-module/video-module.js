class Pattern {

  constructor($element){

    // Store references to DOM elements
    this.$element = $element;

    // Store class properties
    this.video = this.$element.querySelectorAll('.video__video')[0];
    // Init
    this.initSomeThing();

  }

  initSomeThing(){

  }

}

module.exports = Pattern;
