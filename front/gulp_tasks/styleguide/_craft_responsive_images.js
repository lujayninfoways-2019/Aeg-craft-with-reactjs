// Responsive image Craft API mocking
export default function responsiveImages(obj){
  for (let key in obj) {
    if(key === 'RESPONSIVE_IMAGE'){
      let craftAssetTransform = {
        title: 'Dummy image',
        getWidth: (transform) => {
          return transform.width
        },
        getUrl: (transform) => {
          let thisURL = obj[key];
          if (thisURL.includes(".png") || thisURL.includes(".jpg")) {
            return thisURL
          } else {
            return thisURL + '-' + transform.width + 'w.jpg'
          }
        },
        cropPosition: {
          value: 'center-center'
        }
      };
      obj['image'] = craftAssetTransform;
      // Hacky way to ensure .0 or .first are available in certain situations
      obj['image']['0'] = craftAssetTransform;
      obj['image']['first'] = craftAssetTransform;
    }

    if(typeof obj[key] === 'object') {
      responsiveImages(obj[key]);
    }
  }
};
