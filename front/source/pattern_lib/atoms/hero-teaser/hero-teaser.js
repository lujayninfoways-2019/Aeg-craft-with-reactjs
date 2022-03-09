class HeroTeaser {

  constructor(element){

    // Store references to DOM elements
    this.element = element;
    this.windowWidth = window.innerWidth;
    this.contentBox = this.element.querySelector('.hero-teaser__banner-content');

    this.adjustBottomSpace();

    window.addEventListener('resize', () => {
      this.adjustBottomSpace();
    });
  }

  adjustBottomSpace() {
    if (window.innerWidth < 960) {
      let contentBoxOffset = this.contentBox.offsetTop;
      let contentBoxHeight = $(this.contentBox).outerHeight();
      let teaserHeight = this.element.offsetHeight;
      let bottomMargin = contentBoxHeight + contentBoxOffset - teaserHeight;

      this.element.style.marginBottom = bottomMargin + 'px';
    }
  }
}

module.exports = HeroTeaser;
