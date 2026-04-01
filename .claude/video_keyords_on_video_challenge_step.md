Keywords Functionality for Image/Video Steps - Chat History
Request 1: Initial Implementation
You asked to add keywords against video URLs when the validation mode is "video" in image steps. The implementation included:

Migration - Added a nullable json column keywords to the image_submission_guidelines table
Model (ImageSubmissionGuideline.php) - Added $casts for keywords as array
Validation (StoreImageStepRequest.php) - Keywords required as array with min 1 item when mode is video
Controller (ImageStepController) - Clears keywords when mode is not video
JS (custom.js) - Show/hide keywords field based on video mode
Blade Views - Added keywords input UI to admin & company admin create/edit views
Request 2: Show Keywords on View Page
You asked to display keywords on the view.blade.php — added conditional keyword badges when mode is video.

Request 3: Validation Errors Not Showing
You reported keyword validation errors weren't displaying. The issue was the AJAX error handler in custom.js couldn't map Laravel's keywords.0 dot notation to the keywords[] input name. Fixed by routing array field errors to the #keywords-field container.

Request 4: Custom Error Message
You asked to change the ugly default message "The keywords.0 field is required." to "Please add at least one keyword." — added custom messages() method in StoreImageStepRequest.

Files Modified
Migration
ImageSubmissionGuideline.php
StoreImageStepRequest.php
ImageStepController.php
custom.js
Admin create view
Admin edit view
Admin view
Company admin create
Company admin edit
Company admin view
