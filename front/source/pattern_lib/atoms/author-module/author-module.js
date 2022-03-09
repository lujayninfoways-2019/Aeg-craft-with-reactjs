class Ingredients {

  constructor($element){

    // Store references to DOM elements
    this.$element = $element;
    this.decreaseButton = this.$element.querySelectorAll('.button--decrease')[0];

    // Store class properties
    this.someProp = 'some-value';

    // Init
    this.manageIngredients();

  }

  manageIngredients(){
  }
}

module.exports = Ingredients;
