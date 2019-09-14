require('./css-supports-polyfill.js');
require('./polyfill.js');
// var backdropjs = require('backdropjs');
// const toPx = require('to-px');

var domtoimage = require('dom-to-image');

// var addRule = (function (style) {
//     var sheet = document.head.appendChild(style).sheet;
//     return function (selector, css) {
//         var propText = typeof css === "string" ? css : Object.keys(css).map(function (p) {
//             return p + ":" + (p === "content" ? "'" + css[p] + "'" : css[p]);
//         }).join(";");
//         sheet.insertRule(selector + "{" + propText + "}", sheet.cssRules.length);
//     };
// })(document.createElement("style"));


// function backdrop(backdropsource, backdropapply, blur, scroller) {
//     if (document.getElementById("iniframe") == null) {
//         backdropframe = document.createElement("iframe")
//         backdropframe.style.border = "none"
//         backdropframe.width = "100%"
//         backdropframe.height = "100%"
//         var node = backdropapply.appendChild(backdropframe)
//         addbackdrop()

//         return node;
//     }
  
//     function addbackdrop() {
//         backdropdoc = backdropframe.contentWindow.document
//         backdropdoc.open()
//         backdropdoc.write(backdropsource)
//         var markup =
//             '<div id="iniframe"></div><style>body {overflow: hidden; -webkit-filter: blur(' +
//             blur +
//             "px);filter: blur(" +
//             blur +
//             "px); -webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none;}</style>"
//         backdropdoc.write(markup)
//         backdropdoc.close()
//         if (scroller == "yes") {
//             window.addEventListener(
//             "scroll",
//             function(event) {
//                 var scroll = this.scrollY
//                 var scrollx = this.scrollX
//                 var rect = backdropapply.getBoundingClientRect()
//                 scroll = scroll - rect.top
//                 backdropframe.contentWindow.window.scrollTo(scrollx, scroll)
//             },
//             true
//             )
//         }
//     }
// }


if (!window.CSS.supports('backdrop-filter')) {
    // List of all elements with 'backdrop-filter' on page
    var elems = [];

    // List of all fixed elements, will be hidden at render time
    var fixed = [];
    document.body.getElementsByTagName("*").forEach(function (elem) {
        if (window.getComputedStyle(elem, null).getPropertyValue('position') == 'fixed') {
            fixed.push({
                'node': elem,
                'originalOpacity': elem.style.opacity || 1
            })
        }
    });

    console.log(fixed);

    Polyfill({
        declarations: ["backdrop-filter:*"]
    })
    .doMatched(function(rules) {
        // Build list of all elements with backdrop-filter on page
        rules.each(function(rule) {
            var filterVal = rule.getDeclaration()['backdrop-filter'];
            
            document.querySelectorAll(rule.getSelectors()).forEach(function(node) {
                var bgNode = document.createElement('div');
                var isFixed = window.getComputedStyle(node).getPropertyValue('position') == 'fixed';
                Object.assign(bgNode.style, {
                    'position': isFixed ? 'fixed' : 'absolute',
                    'top': node.offsetTop,
                    'left': node.offsetLeft,
                    'width': node.offsetWidth + 'px',
                    'height': node.offsetHeight + 'px',
                    '-webkit-filter': filterVal,
                    '-moz-filter': filterVal,
                    'filter': filterVal
                })
                node.parentNode.insertBefore(bgNode, node);

                if (isFixed) {
                    window.addEventListener(
                        "scroll",
                        function(event) {
                            var scroll = this.scrollY
                            var scrollx = this.scrollX
                            var rect = node.getBoundingClientRect()
                            scroll = scroll - rect.top
                            bgNode.style.backgroundPosition = (-scrollx) + 'px ' + (-2 * node.offsetTop - scroll) + 'px';
                        },
                        true
                    )
                }

                elems.push({
                    'node': node,
                    'bgNode': bgNode,
                    'originalOpacity': node.style.opacity || 1
                });
            });
        });

        var updateBg = function() {
            // first, hide all nodes
            elems.forEach(function(elem) {
                elem.node.style.opacity = 0;
                elem.bgNode.style.opacity = 0;
            });
            fixed.forEach(function(elem) {
                elem.node.style.opacity = 0;
            });

            // then, convert the dom to an image
            domtoimage.toPng(document.body).then(function (dataUrl) {
                // first, restore fixed elements
                fixed.forEach(function(elem) {
                    elem.node.style.opacity = elem.originalOpacity;
                });

                // now, restore opacity of each node and set blurred image
                elems.forEach(function(elem) {
                    elem.node.style.opacity = elem.originalOpacity;
                    elem.bgNode.style.opacity = 1;
                    Object.assign(elem.bgNode.style, {
                        'background': 'url(' + dataUrl + ') no-repeat 0px ' + (-1 * elem.node.offsetTop) + 'px',
                    });
                });
            });
        };
    
        updateBg();
        window.addEventListener("resize", updateBg);

        // rules.each(function(rule) {
        //     var filterVal = rule.getDeclaration()['backdrop-filter'];
        //     var extracted = filterVal.match(/blur\((\w+)\)/)[1];
            
        //     document.querySelectorAll(rule.getSelectors()).forEach(function(elem) {
        //         // backdropjs.backdrop(document.documentElement.outerHTML, elem, toPx(extracted), "yes");

        //         // var blurParent = (function() {
        //         //     var node = document.createElement("div");
        //         //     Object.assign(node.style, {
        //         //         'position': 'absolute',
        //         //         'display': 'block',
        //         //         'overflow': 'hidden',
        //         //         'visibility': 'hidden',
        //         //         'width': '100%',
        //         //         'height': '100%',
        //         //         'top': '0',
        //         //         'left': '0',
        //         //         'z-index': '999999',
        //         //         '-webkit-filter': filterVal,
        //         //         '-moz-filter': filterVal,
        //         //         'filter': filterVal,
        //         //     });
        //         //     return document.body.appendChild(node);
        //         // })();

                
        //         // addRule(rule.getSelectors() + ":before", {
        //         //     'visibility': 'visible',
        //         //     'content': '',
        //         //     'position': 'absolute',
        //         //     'width': '100%',
        //         //     'height': '100%',
        //         //     'top': '0%',
        //         //     'bottom': '0%',
        //         //     'background': 'inherit',
        //         //     '-webkit-filter': filterVal,
        //         //     '-moz-filter': filterVal,
        //         //     'filter': filterVal,
        //         // });

                
        //         // var block = (function() {
        //         //     var node = document.createElement("div");
        //         //     Object.assign(node.style, {
        //         //         'position': 'absolute',
        //         //         'display': 'block',
        //         //         'overflow': 'hidden',
        //         //         'visibility': 'visible',
        //         //         'width': '400px',
        //         //         'height': '200px',
        //         //         'background': 'inherit',
        //         //         'border-radius': '10px',
        //         //     });
        //         //     return blurParent.appendChild(node);
        //         // })();

        //         // var test = (function() {
        //         //     var node = document.createElement("div");
        //         //     Object.assign(node.style, {
        //         //         'display': 'block',
        //         //         'background-color': 'rgba(235,235,235,0.6)',
        //         //     });
        //         //     return block.appendChild(node);
        //         // })();
                
        //         // var updateBg = function() {
        //         //     domtoimage.toPng(document.body).then(function (dataUrl) {
        //         //         Object.assign(blurParent.style, {
        //         //             'background': 'url(' + dataUrl + ') no-repeat'
        //         //         })
        //         //     });
        //         // };
            
        //         // updateBg();
        //         // window.addEventListener("resize", updateBg);
        //     });
        // });
    });
}