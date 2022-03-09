class DetailPage {

  constructor(element){

    // Store references to DOM elements
    this.element = element;

    // Store class properties
    this.openModal = this.element.querySelector('.open-modal');
    this.modalWindow = this.element.querySelector('.modal-window');
    this.ingredientsCounter = this.element.querySelector('.recipe-ingredients__ingredients-counter-amount');
    this.ingredientsList = this.element.querySelector('.recipe-ingredients__ingredients-list');
    this.ingredientsCounterField = this.element.querySelector('#mwServesField');
    this.ingredientsListField = this.element.querySelector('#mwIngredientsField');

    this.openModal.addEventListener('click', () => {
      this.modalTrigger();
    });

  }

  modalTrigger() {
    this.ingredientsCounterField.value = this.ingredientsCounter.value;
    this.ingredientsListField.value = this.ingredientsList.innerText || this.ingredientsList.textContent;
    this.modalWindow.classList.add('modal-window--opened');
  }

}

module.exports = DetailPage;
