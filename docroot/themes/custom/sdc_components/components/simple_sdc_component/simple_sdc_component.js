(function(Drupal) {

  Drupal.behaviors.simpleSdcComponent = {
    attach(context) {
      // Get all buttons with the class 'sdc-button'
      const buttons = document.querySelectorAll('.sdc-button');

      // Loop through each button and add a click event listener
      buttons.forEach((button, index) => {
          button.addEventListener('click', function() {
              // Get the corresponding text element (using index to select the right one)
              const textElement = document.querySelectorAll('.body-text')[index];
              
              // Change the background color of the corresponding text element
              textElement.style.backgroundColor = 'yellow';  // You can change 'yellow' to any color you prefer
          });
      });
    },
  };

})(Drupal);
