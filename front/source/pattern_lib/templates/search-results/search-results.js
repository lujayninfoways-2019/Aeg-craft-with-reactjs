class SearchResults {

  constructor(element){

    // Store references to DOM elements
    this.element = element;
    this.sortByForm = this.element.querySelector('.search-results__form');

    // Change sort by option
    this.sortByForm.addEventListener('change', (e) => {
      e.preventDefault();
      // TODO: Add Ajax and Fade-Effect
      this.sortByForm.submit();
    });
  }
}

module.exports = SearchResults;
