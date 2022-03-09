// Globals
import 'babel-polyfill';
import './_global';
import jQuery from 'jquery';
window.$ = window.jQuery = jQuery;

import jcf from 'jcf';
window.jcf = jcf;


import './utils/jquery.simplePagination';
import './utils/picturefill.min';

import Ingredients from '../pattern_lib/molecules/ingredients/ingredients';
import Video from '../pattern_lib/molecules/video-module/video-module';
import Header from '../pattern_lib/molecules/header/header';
import HeroTeaser from '../pattern_lib/atoms/hero-teaser/hero-teaser';
import DetailPage from '../pattern_lib/templates/detail-page/detail-page';
import ModalWindow from '../pattern_lib/molecules/modal-window/modal-window';
import RecipeResults from '../pattern_lib/templates/recipe-results/recipe-results';
import Comments from '../pattern_lib/atoms/comments/comments';
import SearchResults from '../pattern_lib/templates/search-results/search-results';
import RecipeFilters from '../pattern_lib/molecules/recipe-filters/recipe-filters';
import ContactForm from '../pattern_lib/molecules/form/form';
import Cookie from '../pattern_lib/molecules/cookie-disclaimer/cookie-disclaimer';
import Accordion from '../pattern_lib/molecules/accordion/accordion';
import TrackingHelper from './modules/tracking.js';

class Site {
  constructor() {
    this.parseContent();
    new TrackingHelper();
  }

  find(selector) {
    if (this.root.matches) {
      return this.root.matches(selector) ? [this.root] : [...this.root.querySelectorAll(selector)];
    } else {
      return this.root.msMatchesSelector(selector) ? [this.root] : [...this.root.querySelectorAll(selector)];
    }
  }

  parseContent(root = document.body) {
    this.root = root;

    this.find(`a[href='#']`).forEach((el) => {
      el.addEventListener('click', (event) => {
        event.preventDefault();
      });
    });

    this.find('.form__select').forEach((el) => {
      jcf.replace(el, '', {
        wrapNative: false,
        wrapNativeOnMobile: true,
        fakeDropInBody: false
      })
    });

    this.find('.recipe-ingredients__ingredients').forEach((element) => {
      new Ingredients(element);
    });

    this.find('.video__video-container').forEach((element) => {
      new Video(element);
    });

    this.find('.header').forEach((element) => {
      new Header(element);
    });

    this.find('.hero-teaser').forEach((element) => {
      new HeroTeaser(element);
    });

    this.find('.m-accordion').forEach((element) => {
      new Accordion(element);
    });

    this.find('.m-form').forEach((element) => {
      new ContactForm(element);
    });

    this.find('.m-cookie').forEach((element) => {
      new Cookie(element);
    });

    this.find('.pagination__container:not(.craft-paginate)').forEach((element) => {
      let items = [...document.querySelectorAll('.category-page__articles .recipe-teaser')];
      let itemsToShow = items.slice(0, 5);
      let numberOfItems = items.length;
      let pathName = window.location.pathname;
      $(element).pagination({
        edges: 1,
        displayedPages: 2,
        items: numberOfItems,
        itemsOnPage: 5,
        cssStyle: 'light-theme',
        hrefTextPrefix: `${pathName}#`,
        ellipsePageSet: false,
        prevText: '<span class="icon icon-Arrow_Left_small"></span>',
        nextText: '<span>next site</span><span class="icon icon-Arrow_Right_small"></span>'
      })
    });

    // craft pagination with ajax
    $( "body" ).on( "click", ".pagination__container.craft-paginate a", function( event ) {
      event.preventDefault();

      let link = $(this);
      let container = link.closest('.results-ajax');

      container.fadeTo('slow', 0, function(){
        container.load(link.attr('href') + ' .results-ajax > *', function(){
          container.fadeTo('slow', 1);
        });
      });
    });

    this.find('.detail-page').forEach((element) => {
      new DetailPage(element);
    });

    this.find('.modal-window').forEach((element) => {
      new ModalWindow(element);
    });

    this.find('.recipe-results').forEach((element) => {
      new RecipeResults(element);
    });

    this.find('.comments').forEach((element) => {
      new Comments(element);
    });

    this.find('.search-results').forEach((element) => {
      new SearchResults(element);
    });

    this.find('.recipe-filters').forEach((element) => {
      new RecipeFilters(element);
    });

  }
}

new Site();
