class RecipeFilters {

  constructor(element){

    // Store references to DOM elements
    this.element = element;
    this.recipeFiltersForm = this.element.querySelector('.recipe-filters__form');
    this.resetButton = this.recipeFiltersForm.querySelector('.recipe-filters__clear-filters');
    this.allCheckboxes = this.recipeFiltersForm.querySelectorAll('input[type="checkbox"]');

    this.recipeFiltersForm.addEventListener('change', (event) => {
      this.postrecipeFiltersForm(event, $(this.recipeFiltersForm));
    });

    this.resetButton.addEventListener('click', (event) => {
      event.preventDefault();
      //this.recipeFiltersForm.reset();
      this.allCheckboxes.forEach(function(checkbox) {
        checkbox.checked = false;
      });
      this.postrecipeFiltersForm(event, $(this.recipeFiltersForm));
    });
  }

  postrecipeFiltersForm(event, form){

    $.ajax({
      url: '/actions/recipe/getRecipeCount',
      data: form.serialize(),
      processData: false,
      contentType: false,
      type: 'POST',
      success: function(data){
        $('#recipe-count').text(data['count']);
      },
      fail: function(data) {
        alert('Error');
      }
    });
  }

}

module.exports = RecipeFilters;
