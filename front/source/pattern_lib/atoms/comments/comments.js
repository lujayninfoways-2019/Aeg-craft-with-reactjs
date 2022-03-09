class Comments {

  constructor(element){

    // Store references to DOM elements
    this.element = element;
    this.commentsContainer = this.element.querySelector('.comments__container');
    this.commentsButton = this.element.querySelector('.comments__trigger');

    // Init
    // done in craft layout
    //this.initDisqus();

    //Show comments
    this.commentsButton.addEventListener('click', () => {
      this.showComments();
    });

  }

  showComments() {
    this.commentsContainer.classList.toggle('comments__container--visible');
    this.commentsButton.classList.toggle('comments__trigger--opened');
  }

  initDisqus(){
      let disqus_config = function () {
      this.page.url = window.disqusUrl;
      this.page.identifier = window.disqusIdentifier;
      this.page.title = window.disqusTitle;
    };

    let s = document.createElement('script');
    s.src = '//aeg-geschmackssachen.disqus.com/embed.js';  // IMPORTANT: Replace EXAMPLE with your forum shortname!
    s.async = true;
    s.type = "text/javascript";

    s.setAttribute('data-timestamp', +new Date());
    (document.head || document.body).appendChild(s);
  }
}

module.exports = Comments;
