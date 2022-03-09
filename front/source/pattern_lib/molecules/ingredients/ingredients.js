import math from 'mathjs';

class Ingredients {

  constructor(element){

    // Store references to DOM elements
    this.element = element;
    this.decreaseButton = this.element.querySelector('.button--decrease');
    this.increaseButton = this.element.querySelector('.button--increase');
    this.amountFiled = this.element.querySelector('.recipe-ingredients__ingredients-counter-amount');
    this.amountFiledMin = this.amountFiled.min;
    this.amountFiledMax = this.amountFiled.max;
    this.ingredientQuantity = [...this.element.querySelectorAll('.recipe--ingredients__quantity')];
    this.ingredientQuantity.forEach((element) => {
      element.getAttribute("data-quantity").replace(/,/g, '.');
      element.innerHTML = element.innerHTML.replace(/,/g, '.');
      this.roundQuantity(element);
    });
    this.ingredientUnit = [...this.element.querySelectorAll('.recipe-ingredients__unit')];

    this.manageIngredients(this.amountFiledMin, this.amountFiledMax);
  }

  getFractionRatio (value) {
     return math.format(value, {fraction: 'ratio'});
  }


  toFraction (value) {
    let fraction;
    let fractionString;
    let roundedFraction;

    fraction = value  % 1;
    roundedFraction = Math.round(fraction * 1000000)/1000000;
    //input string for 0.3333 has to be "0.(3333)"
    //fractionString = (roundedFraction).toString().replace(/\.(\d+)/, ".($1)");
    fraction = this.getFractionRatio(math.fraction(roundedFraction));
    value = Math.floor(value) > 0 ? Math.floor(value) + " " + fraction: fraction;
    return value;
  }

  roundQuantity(value) {
    let roundedValue;
    if (value.getAttribute("data-quantity") % 2 === 0) {
      roundedValue = Math.round(value.getAttribute("data-quantity") * 100)/100;
    } else {
      roundedValue = Math.round(value.getAttribute("data-quantity") * 1000000)/1000000;
    }
    if (roundedValue > 0 && value.getAttribute("data-quantity").length > 0) {
      value.setAttribute("data-quantity", roundedValue);
      if (roundedValue % 1 !== 0) {
        roundedValue = this.toFraction(roundedValue);
      }
      value.innerHTML = roundedValue;
    }
  }

  manageIngredients(min, max){
    let ingreadientsChangeValue = [];
    this.ingredientQuantity.forEach((quantity, i) => {
      ingreadientsChangeValue[i] = Number(this.ingredientQuantity[i].getAttribute("data-quantity") / this.amountFiled.value);
    });

    this.decreaseButton.addEventListener('click', () => {
      let inputValue = this.amountFiled.value;
      let newValue = inputValue - 1;
      if (newValue > min) {
        this.amountFiled.value = newValue;
        this.ingredientQuantity.forEach((quantity, i) => {
          if (quantity.getAttribute("data-quantity").length > 0) {
            quantity.setAttribute("data-quantity", Number(quantity.getAttribute("data-quantity")) - ingreadientsChangeValue[i]);
          }
          this.roundQuantity(quantity);
          this.setQuantityUnit(quantity);
        });
      }
    });

    this.increaseButton.addEventListener('click', () => {
      let inputValue = this.amountFiled.value;
      inputValue++;
      let increasedValue = inputValue;
      if (increasedValue <= max) {
        this.amountFiled.value = increasedValue;
        this.ingredientQuantity.forEach((quantity, i) => {
          if (quantity.getAttribute("data-quantity").length > 0) {
            quantity.setAttribute("data-quantity", Number(quantity.getAttribute("data-quantity")) + ingreadientsChangeValue[i] );
          }
          this.roundQuantity(quantity);
          this.setQuantityUnit(quantity);
        });
      }
    });
  }

  setQuantityUnit(quantity) {
    let unit = quantity.nextElementSibling;
    let unitSingular = unit.getAttribute("data-singular");
    let unitPlural = unit.getAttribute("data-plural");
    let unitSymbol = unit.getAttribute("data-unit");
    let currentQuantity = quantity.getAttribute("data-quantity");
    if (currentQuantity <= 1 && (unitPlural !== unitSingular)) {
      unit.innerHTML = ` ${unitSingular}`;
    } else if (currentQuantity > 1 && (unitPlural !== unitSingular)) {
      unit.innerHTML = ` ${unitPlural}`;
    } else {
      unit.innerHTML = `${unitSymbol}`;
    }
  }
}

module.exports = Ingredients;
