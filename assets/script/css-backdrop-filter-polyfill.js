require('./css-supports-polyfill.js');
require('./polyfill.js');

var domtoimage = require('dom-to-image');
var toPx = require('to-px');

if (!window.CSS.supports('backdrop-filter', 'blur(1px)')) {
    // List of all elements with 'backdrop-filter' on page
    var elems = [];

    Polyfill({
        declarations: ["backdrop-filter:*"]
    })
    .doMatched(function(rules) {
        // Build list of all elements with backdrop-filter on page
        rules.each(function(rule) {
            var filterVal = rule.getDeclaration()['backdrop-filter'];
            var extracted = '0px';
            try {
                extracted = filterVal.match(/blur\((\w+)\)/)[1];
            } catch (e) { };
            
            document.querySelectorAll(rule.getSelectors()).forEach(function(node) {
                var containerNode = document.createElement('div');
                Object.assign(containerNode.style, {
                    'overflow': 'hidden',
                });
                node.parentNode.insertBefore(containerNode, node);

                var bgNode = document.createElement('div');
                Object.assign(bgNode.style, {
                    'width': 'calc(100% + 2 * ' + extracted + ')',
                    'height': 'calc(100% + 2 * ' + extracted + ')',
                    'position': 'absolute',
                    'top': '-' + extracted,
                    'left': '-' + extracted,
                    '-webkit-filter': filterVal,
                    '-moz-filter': filterVal,
                    'filter': filterVal
                })
                containerNode.appendChild(bgNode);

                var isFixed = window.getComputedStyle(node).getPropertyValue('position') == 'fixed';
                var elem = {
                    'node': node,
                    'containerNode': containerNode,
                    'bgNode': bgNode,
                    'originalOpacity': node.style.opacity || 1,
                    'scroll': isFixed,
                    'offset': toPx(extracted),
                }
                elems.push(elem);

                window.addEventListener(
                    "scroll",
                    function(event) {
                        if (elem.scroll) {
                            var scrollY = -1 * node.offsetTop  + elem.offset - this.scrollY;
                            var scrollX = -1 * node.offsetLeft + elem.offset - this.scrollX;
                            bgNode.style.backgroundPosition = scrollX + 'px ' + scrollY + 'px';
                        }
                    },
                    true
                )
            });
        });

        var updateBg = function() {
            // first, hide all nodes
            elems.forEach(function(elem) {
                elem.node.style.opacity = 0;
                elem.containerNode.style.opacity = 0;
            });

            function renderElem(node) {
                var isFixed = false;
                try {
                    isFixed = window.getComputedStyle(node, null).getPropertyValue('position') == 'fixed';
                } catch (e) { }
                return !isFixed;
            }

            // then, convert the dom to an image
            domtoimage.toPng(document.body, {filter: renderElem}).then(function (dataUrl) {
                // now, restore opacity of each node and set blurred image
                elems.forEach(function(elem) {
                    elem.node.style.opacity = elem.originalOpacity;
                    elem.containerNode.style.opacity = 1;
                    var isFixed = window.getComputedStyle(elem.node).getPropertyValue('position') == 'fixed';
                    elem.scroll = isFixed;
                    Object.assign(elem.containerNode.style, {
                        'position': isFixed ? 'fixed' : 'absolute',
                        'top': elem.node.offsetTop + 'px',
                        'left': elem.node.offsetLeft + 'px',
                        'width': elem.node.offsetWidth + 'px',
                        'height': elem.node.offsetHeight + 'px',
                        'border-top-left-radius': window.getComputedStyle(elem.node).getPropertyValue('border-top-left-radius'),
                        'border-top-right-radius': window.getComputedStyle(elem.node).getPropertyValue('border-top-right-radius'),
                        'border-bottom-right-radius': window.getComputedStyle(elem.node).getPropertyValue('border-bottom-right-radius'),
                        'border-bottom-left-radius': window.getComputedStyle(elem.node).getPropertyValue('border-bottom-left-radius'),
                    });
                    var posX = -1 * elem.node.offsetLeft + elem.offset;
                    var posY = -1 * elem.node.offsetTop  + elem.offset;
                    Object.assign(elem.bgNode.style, {
                        'background': window.getComputedStyle(document.body, null).getPropertyValue('background-color') + ' url(' + dataUrl + ') no-repeat ' + posX + 'px ' + posY + 'px',
                    });
                });
            });
        };
    
        updateBg();
        window.addEventListener("resize", updateBg);
    });
}