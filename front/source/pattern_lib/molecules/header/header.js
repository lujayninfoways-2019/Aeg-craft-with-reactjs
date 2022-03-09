class Header {

  constructor(element){

    // Store references to DOM elements
    this.element = element;
    this.nav = this.element.querySelector('.header__nav-list');
    this.navOffsetTop = this.nav.offsetTop;
    this.logo = this.element.querySelector('.header__logo');
    this.logoHeight = this.logo.offsetHeight;
    this.headerTeasers = this.element.querySelector('.header__teasers');
    this.headerSibling = document.getElementsByClassName('header')[0].nextElementSibling;
    this.headerSearchBar = this.element.querySelectorAll('.header__nav-searchbar')[0];
    this.body = document.getElementsByTagName('body')[0];
    this.navbarPosition = 0;
    this.shareViaTwitterButton = this.element.querySelector('.header__nav-shares-link--twitter');
    this.shareViaFacebookButton = this.element.querySelector('.header__nav-shares-link--facebook');
    this.shareViaMailButton = this.element.querySelector('.header__nav-shares-link--mail');

    //recipe flayout elements
    this.recipeFilters = this.element.querySelector('.recipe-filters');
    this.recipeFiltersSwitcher = this.recipeFilters.querySelectorAll('[name="filter"]');
    this.recipeForm = this.recipeFilters.querySelectorAll('.recipe-filters__form')[0];

    //mobile menu elements
    this.mobileItems = this.element.querySelectorAll('.header__nav-item--mobile');
    this.mobileList = this.element.querySelector('.header__nav-list--mobile');

    //dropdowns
    this.dropdownTriggers = this.element.querySelectorAll('.header__nav-icon[data-show], .header__nav-item[data-show]');
    this.windowWidth = window.innerWidth;

    //check if user scrolls down
    window.addEventListener('scroll', () => {
      if ($(window).scrollTop() > this.navOffsetTop && (this.windowWidth > 960)) {
        this.handleStickyHeader(true);
      } else if ($(window).scrollTop() > this.navOffsetTop && (this.windowWidth < 960)) {
        this.handleStickyHeader();
      } else if ($(window).scrollTop() <= this.navOffsetTop) {
        this.element.classList.remove('header--sticky');
        this.headerSibling.removeAttribute('style');
      }
    });

    //initialize mobile menu

    if (this.windowWidth < 960) {
      this.initMobileMenu();
    }

    window.addEventListener('resize', () => {
      if (this.windowWidth < 960) {
        this.handleRecipesFiltersResize();
        this.initMobileMenu();
      } else {
        this.initDesktopMenu();
      }
    });

    //dropdowns toggle handler
    [...this.dropdownTriggers].map((el) => {
      return el.addEventListener('click', (event) => {
        this.dropdownToggle(event);
        this.handleScroll(event);
      });
    });

    [...this.recipeFiltersSwitcher].map((el) => {
      return this.manageFilters(el);
    });

    if (this.shareViaTwitterButton) {
      this.shareViaTwitterButton.addEventListener('click', (e) => {
        e.preventDefault();
        const pageUrl = window.location;
        window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(pageUrl)}`);
      });
    }

    if (this.shareViaFacebookButton) {
      this.shareViaFacebookButton.addEventListener('click', (e) => {
        e.preventDefault();
        const pageUrl = window.location;
        FB.ui({
          method: 'share',
          href: pageUrl.href,
        }, function(response){

        });
      });
    }

    if (this.shareViaMailButton) {
      this.shareViaMailButton.addEventListener('click', (e) => {
        e.preventDefault();
        const pageUrl = window.location;
        const subject = document.title;
        const body = pageUrl;
        location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
      });
    }
  }

  handleStickyHeader(desktop) {
    this.element.classList.add('header--sticky');
    if (desktop && this.headerTeasers) {
      this.headerSibling.style.marginTop = `${410}px`;
    } else if (desktop && !this.headerTeasers) {
      this.headerSibling.style.marginTop = `${200}px`;
    } else if (!desktop && this.headerTeasers) {
      this.headerSibling.style.marginTop = `${225}px`;
    } else if (!desktop) {
      this.headerSibling.style.marginTop = `${70}px`;
    }
  }

  initMobileMenu() {
    this.mobileList.appendChild(this.headerSearchBar);
    this.headerSearchBar.classList.add('.header__nav-item--mobile');
  }

  initDesktopMenu() {
    if (this.mobileList.classList.contains('dropdown--opened')) {
      this.element.querySelector('.header__nav-opener').classList.remove('header__nav-item--selected');
      this.mobileList.classList.remove('dropdown--opened');
      $(this.headerSearchBar).siblings('.dropdown__overlay').remove();
    }
    this.element.appendChild(this.headerSearchBar);
    this.headerSearchBar.classList.remove('.header__nav-item--mobile');
  }

  dropdownToggle(event) {
    event.preventDefault ? event.preventDefault() : (event.returnValue = false);

    let trigger = event.currentTarget;
    let targetName = $(event.currentTarget).data('show');
    let target = this.element.querySelector('[data-target="' + targetName + '"]');
    let dropdowns = this.element.querySelectorAll('.dropdown:not([data-target="' + targetName + '"])');
    let selectedTriggers = $(event.target).closest('.header__nav-item').siblings();

    [...dropdowns].map((el) => {
      return el.classList.remove('dropdown--opened');
    });

    if (selectedTriggers) {
      selectedTriggers.removeClass('header__nav-item--selected');
    }

    if (targetName === 'recipe-filter' && window.innerWidth < 960) {
      this.handleRecipesFiltersResize();
    }

    if (targetName !== 'recipe-filter') {

      let existingOverlay = $('.dropdown__overlay');

      if (existingOverlay.length) {
        $(existingOverlay).remove();
      }

      let overlay = document.createElement('div');
      overlay.classList.add('dropdown__overlay');
      target.appendChild(overlay);
      let appendedOverlay = document.querySelector('.dropdown__overlay');
      let maxHeight = $(document).outerHeight() - $(appendedOverlay).offset().top;
      overlay.style.height = maxHeight + 'px';
      overlay.addEventListener('click', () => {
        this.clickOverlay(target, trigger)
      });

    }

    trigger.classList.toggle('header__nav-item--selected');
    target.classList.toggle('dropdown--opened');
  }

  handleScroll(event) {
    if (event.currentTarget.classList.contains('header__nav-item--selected')) {
      let isSticky = this.element.classList.contains('header--sticky');
      this.navbarPosition = $(this.element).offset().top;
      this.recipeFilters.style.overflowY = 'scroll';
      // this.handleRecipesFiltersResize();
      // this.body.classList.add('overflow--hidden');
      if (isSticky) {
        setTimeout(() => {
          this.element.classList.add('header--sticky');
        }, 50);
      }
    } else {
      // this.body.classList.remove('overflow--hidden');
      $(this.body).scrollTop(this.navbarPosition);
    }
  }

  handleRecipesFiltersResize() {
    this.recipeFilters.style.height = `${window.innerHeight - this.element.offsetHeight}px`;
  }

  manageFilters(el) {
    el.addEventListener('change', (event) => {
      let eventTrigger = $(event.target);
      let eventTriggerButton = eventTrigger.next('.recipe-filters__button');
      if (eventTrigger.prop('checked')) {
        eventTriggerButton.addClass('recipe-filters__button--selected');
      } else {
        eventTriggerButton.removeClass('recipe-filters__button--selected');
      }
    });
  }

  clickOverlay(target, trigger) {
    target.classList.remove('dropdown--opened');
    trigger.classList.remove('header__nav-item--selected');
    $(this).remove();
  }
}

module.exports = Header;
