class RecipeResults {

  constructor(element){

    // Store references to DOM elements
    this.element = element;

    // Store class properties
    this.appliedFilters = this.element.querySelectorAll('.recipe-results__filter-button');
    this.appliedFiltersArray = [...this.appliedFilters];
    this.appliedFiltersForm = this.element.querySelector('.recipe-results__filter-buttons');

    // Init
    this.appliedFiltersForm.addEventListener('change', (event) => {
      this.updateAppliedFilters(event);
    });

    this.appliedFiltersArray.map((element) => {
      return element.addEventListener('click', (event) => {
        this.handleAppliedFilters(event);
      });
    });
  }

  updateAppliedFilters(event){
    $(this.appliedFiltersForm).submit();
  }

  handleAppliedFilters(event){
    let clickedButton = event.currentTarget;
    clickedButton.style.display = 'none';
  }

}

module.exports = RecipeResults;
