$(document).ready(function() {
    $("#updateButton").click(function() {
      // Make an AJAX request to fetch new content
      $.get("dynamic_content.php", function(response) {
        // Update the content dynamically
        $("#dynamicContent").html(response);
      });
    });
  });