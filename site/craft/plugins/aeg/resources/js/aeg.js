$( document ).ready(function() {

    $('#container').on('submit', function(e) {
      $('#fields-category').find('.element').each(function(index, $element) {
        if($element.querySelector('.title').innerHTML === 'Geräte') {
          $('#fields-mainIngredient').find('input').attr('disabled', true);
          $('#fields-diet').find('input').attr('disabled', true);
          $('#fields-recipeCategory').find('input').attr('disabled', true);
        } else {
          $('#fields-mainIngredient').find('input').attr('disabled', false);
          $('#fields-diet').find('input').attr('disabled', false);
          $('#fields-recipeCategory').find('input').attr('disabled', false);
        }
       });
    });

    $('#fields-category').find('.element').each(function(index, $element) {
      if($element.querySelector('.title').innerHTML === 'Geräte') {
        $('#fields-mainIngredient-field').hide();
        $('#fields-diet-field').hide();
        $('#fields-recipeCategory-field').hide();
      } else {
        $('#fields-mainIngredient-field').show();
        $('#fields-diet-field').show();
        $('#fields-recipeCategory-field').show();
      }
    });
  
    $('#fields-category').data('elementSelect').on('selectElements', function(e) {
      e.target.$elements.each(function(index, $element) {
          if($element.querySelector('.title').innerHTML === 'Geräte') {
            $('#fields-mainIngredient-field').hide();
            $('#fields-diet-field').hide();
            $('#fields-recipeCategory-field').hide();
            
          } else {
            $('#fields-mainIngredient-field').show();
            $('#fields-diet-field').show();
            $('#fields-recipeCategory-field').show();
          }
      });
    });
    
    $('#fields-category').data('elementSelect').on('removeElements', function(e) {
        e.target.$elements.each(function(index, $element) {
            if($element.querySelector('.title').innerHTML === 'Geräte') {
              $('#fields-mainIngredient-field').hide();
              $('#fields-diet-field').hide();
              $('#fields-recipeCategory-field').hide();
              
            } else {
              $('#fields-mainIngredient-field').show();
              $('#fields-diet-field').show();
              $('#fields-recipeCategory-field').show();
            }
        });     
    });  
});
