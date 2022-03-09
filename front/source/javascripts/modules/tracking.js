export default class TrackingHelper {
    constructor() {
    this.attachTracking('event',  $('[data-track-event]'));
    this.attachTracking('social',  $('[data-track-social]'));
  }

  get trackerAvailable() {
    return typeof window.ga == 'function'
  }

  attachTracking(trackingType, $trackedEls) {
    let handler;
    switch(trackingType) {
      case 'event':
        handler = this.trackEvent.bind(this);
      break;
      case 'social':
        handler = this.trackSocial.bind(this);
      break;
    }

    $trackedEls.on('click', ev => {
      let $target = $(ev.currentTarget);
      let trackCat = $target.data('track-category');
      let trackAction = $target.data('track-action'); 

      handler(trackCat, trackAction);
    });
      
  }

  

  trackEvent(cat, action) {
    if(!this.trackerAvailable) return;
    if(!cat || !action) return;

    ga('send', 'event', cat, action);
  }

  trackSocial(network, action, targetOption) {
    if(!this.trackerAvailable) return;
    if(!network || !action) return;

    let target = targetOption || window.location.href;

    ga('send', 'social', network, action, target);
  }
}