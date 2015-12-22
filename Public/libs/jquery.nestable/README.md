Nestable 可移动拖拽的树型结构的使用(jQuery)
时间 2014-08-22 03:25:08  Just Code
原文  http://justcoding.iteye.com/blog/2107210
主题 jQuery WebKit HTML
利用jQuery可以制作出很好的树型结构。这里介绍一款最近才找到使用的Nestable

可以拖动。  网页中的效果 http://dbushell.github.com/Nestable/

具体详细介绍的地址下载 https://github.com/dbushell/Nestable
精简后的实例：


<style type="text/css">
/**
 * Nestable
 */

.nestable { position: relative; display: block; margin: 0; padding: 0; max-width: 600px; list-style: none; font-size: 13px; line-height: 20px; }

.dd-list { display: block; position: relative; margin: 0; padding: 0; list-style: none; }
.dd-list .dd-list { padding-left: 30px; }
.dd-collapsed .dd-list { display: none; }

.dd-item,
.dd-empty,
.dd-placeholder { display: block; position: relative; margin: 0; padding: 0; min-height: 20px; font-size: 13px; line-height: 20px; }

.dd-handle { display: block; height: 30px; margin: 5px 0; padding: 5px 10px; color: #333; text-decoration: none; font-weight: bold; border: 1px solid #ccc;
  background: #fafafa;
  background: -webkit-linear-gradient(top, #fafafa 0%, #eee 100%);
  background:	-moz-linear-gradient(top, #fafafa 0%, #eee 100%);
  background:		 linear-gradient(top, #fafafa 0%, #eee 100%);
  -webkit-border-radius: 3px;
      border-radius: 3px;
  box-sizing: border-box; -moz-box-sizing: border-box;
}
.dd-handle:hover { color: #2ea8e5; background: #fff; }

.dd-item > button { display: block; position: relative; cursor: pointer; float: left; width: 25px; height: 20px; margin: 5px 0; padding: 0; text-indent: 100%; white-space: nowrap; overflow: hidden; border: 0; background: transparent; font-size: 12px; line-height: 1; text-align: center; font-weight: bold; }
.dd-item > button:before { content: '+'; display: block; position: absolute; width: 100%; text-align: center; text-indent: 0; }
.dd-item > button[data-action="collapse"]:before { content: '-'; }

.dd-placeholder,
.dd-empty { margin: 5px 0; padding: 0; min-height: 30px; background: #f2fbff; border: 1px dashed #b6bcbf; box-sizing: border-box; -moz-box-sizing: border-box; }
.dd-empty { border: 1px dashed #bbb; min-height: 100px; background-color: #e5e5e5;
  background-image: -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
            -webkit-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
  background-image:	-moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
             -moz-linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
  background-image:		 linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff),
                linear-gradient(45deg, #fff 25%, transparent 25%, transparent 75%, #fff 75%, #fff);
  background-size: 60px 60px;
  background-position: 0 0, 30px 30px;
}

.dd-dragel { position: absolute; pointer-events: none; z-index: 9999; }
.dd-dragel > .dd-item .dd-handle { margin-top: 0; }
.dd-dragel .dd-handle {
  -webkit-box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
      box-shadow: 2px 4px 6px 0 rgba(0,0,0,.1);
}

  </style>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="jquery.nestable.js"></script>
Nestable is an experimental example and not under active development. If it suits your requirements feel free to expand upon it!

Usage

Write your nested HTML lists like so:

<div class="dd">
    <ol class="dd-list">
        <li class="dd-item" data-id="1">
            <div class="dd-handle">Item 1</div>
        </li>
        <li class="dd-item" data-id="2">
            <div class="dd-handle">Item 2</div>
        </li>
        <li class="dd-item" data-id="3">
            <div class="dd-handle">Item 3</div>
            <ol class="dd-list">
                <li class="dd-item" data-id="4">
                    <div class="dd-handle">Item 4</div>
                </li>
                <li class="dd-item" data-id="5">
                    <div class="dd-handle">Item 5</div>
                </li>
            </ol>
        </li>
    </ol>
</div>
Then activate with jQuery like so:

$('.dd').nestable({ /* config options */ });
Events

The change event is fired when items are reordered.

$('.dd').on('change', function() {
    /* on change event */
});
Methods

You can get a serialised object with all data-* attributes for each item.

$('.dd').nestable('serialize');
The serialised JSON for the example above would be:

[{"id":1},{"id":2},{"id":3,"children":[{"id":4},{"id":5}]}]
Configuration

You can change the follow options:

maxDepth number of levels an item can be nested (default 5)
group group ID to allow dragging between lists (default 0)
These advanced config options are also available:

listNodeName The HTML element to create for lists (default 'ol')
itemNodeName The HTML element to create for list items (default 'li')
rootClass The class of the root element .nestable() was used on (default 'dd')
listClass The class of all list elements (default 'dd-list')
itemClass The class of all list item elements (default 'dd-item')
dragClass The class applied to the list element that is being dragged (default 'dd-dragel')
handleClass The class of the content element inside each list item (default 'dd-handle')
collapsedClass The class applied to lists that have been collapsed (default 'dd-collapsed')
placeClass The class of the placeholder element (default 'dd-placeholder')
emptyClass The class used for empty list placeholder elements (default 'dd-empty')
expandBtnHTML The HTML text used to generate a list item expand button (default '<button data-action="expand">Expand></button>')
collapseBtnHTML The HTML text used to generate a list item collapse button (default '<button data-action="collapse">Collapse</button>')
Inspect the Nestable Demo for guidance." style="width:400px;height:240px;"></textarea>