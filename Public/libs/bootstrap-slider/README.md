http://www.eyecon.ro/bootstrap-slider/


Using bootstrap-slider.js(Call the slider via javascript):
    $('.slider').slider();

Options
Name        type        default         description
-----------------------------------------------------------------------------------------------
min         float       0               minimum possible value
max         float       10              maximum possible value
step        float       1               increment step
orientation string      'horizontal'    set the orientation. Accepts 'vertical' or 'horizontal'
value       float|array 5               initial value. Use array to have a range slider.
selection   string      'before'        selection plament. Accepts: 'before', 'after' or 'none'. In case of a range slider, the selection will be placed between the handles
tooltip     string      'show'          whatever to show or hide the tooltip. Accepts: 'show' or 'hide'
handle      string      'round'         handle shape. Accepts: 'round', 'square' or 'triangle'
formater    function    returns the plain value	formater callback. Return the value wanted to be displayed in the tooltip


Markup (You can use data attributes to set up the configuration.):
<input type="text" class="span2" value=""
    data-slider-min="-20"
    data-slider-max="20"
    data-slider-step="1"
    data-slider-value="-14"
    data-slider-orientation="vertical"
    data-slider-selection="after"
    data-slider-tooltip="hide" />

Methods:
.slider(options)
    Initializes a slider.
.slider('getValue')
    Get the value.
.slider('setValue', value)
    Set a new value.

Events (Slider class exposes a few events for manipulating the value.):
Event       Description
-----------------------------------------------------------
slideStart  This event fires when a dragging starts.
slide       This event is fired when the slider is dragged.
slideStop   This event is fired when a dragging stops.