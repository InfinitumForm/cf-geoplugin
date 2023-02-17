(function($) {
  $.fn.cfgeoMap = function() {
    var self = this;
    //define constants
    if (typeof arguments[0] === "string") {
      action = arguments[0];
      options = arguments[1] || null;
      callback = arguments[2] || null;
    } else if (typeof arguments[0] === "object") {
      action = "create";
      options = arguments[0];
      callback = arguments[1] || null;
    } else if (typeof arguments[0] === "function") {
      action = "create";
      options = null;
      callback = arguments[0];
    } else {
      action = "create";
      options = null;
      callback = null;
    }
	
    var destroy = function(options, callback) {
        var that = self;
        self.remove();
        if (typeof callback === "function") callback();
        return;
      }
    var addText = function(p, text, x, y, color, font, fontSize)
    {
      try {
        var t = document.createElementNS("http://www.w3.org/2000/svg", "text");
        if (p && typeof p.getBBox === "function" && x && y) {
          var b = p.getBBox();
          t.setAttribute("transform", "translate(" + x + " " + y + ")");
          t.textContent = text;
          t.setAttribute("id", text); 
          t.setAttribute("fill", color);
          t.setAttribute("font-size", fontSize);
          t.setAttribute("font-family", font)
          p.parentNode.insertBefore(t, p.nextSibling);
        }
      }
      catch(err) {
        if (err) {
          
        }
      }
    }
    
    var addLabelRectangle = function(text, y, fill, stroke, color, font, fontSize) {
      try {
        // Set any attributes as desired
        var rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        p = document.getElementsByTagName('svg')[0];
        var viewbox = p.viewBox.baseVal;
        rect.setAttribute("id", text); 
        rect.setAttribute("fill",fill);
        rect.setAttribute("stroke",stroke);
        rect.setAttribute("stroke-width","1");
        rect.setAttribute("x", (p.getBBox().width-40));
        rect.setAttribute("y", y);
        rect.setAttribute("width", "40");
        rect.setAttribute("height", "20");
        
        // Add to a parent node; document.documentElement should be the root svg element.
        // Acquiring a parent element with document.getElementById() would be safest.
        p.appendChild(rect);
        addText(rect, text, p.getBBox().width-30, y+15, color, font, fontSize);
      }
      catch(err) {
        if (err) {
        
        }
      }
    }
    var create = function(options, callback) {
        var settings = $.extend({
          // These are the defaults.
          map: "world",
          customMap: false,
          territories: true,
          antarctica: false,
          color: "#999",
		  colorSelected: "#cc0000",
          border: "white",
          align: "center",
          backgroundColor: "#white",
          borderWidth: "2",
          height: null,
          //default is actually 100%
          width: null,
          //default is actually 100%
          click: function() {
			return;
          },
          hover: function() {
            return;
          },
          blur: function() {
            return;
          },
          hoverColor: "#333",
          hoverBorder: "#f2f2f2",
          littleRedBook: true,
          hideCountries: false,
          disableCountries: [],
          individualCountrySettings: false,
          labels: false,
          labelAttributes: {
            color: '#333',
            font: 'Helvetica, Verdana, Verdana, sans-serif',
            fontSize: '15'
            
          }
        }, options);
        // add the SVG to the div
        var that = self;
        that.css({
          width: (settings.width || ""),
          height: (settings.height || ""),
        });
        if (settings.map == "world") {
          var file = (settings.antarctica) ? 'world-map-with-antarctica.svg' : 'world-map.svg';
        } else if (settings.map == "usa-countries") {
          var file = 'usa-counties.svg';
        } else if (settings.map == "usa") {
          var file = 'blank-usa-territories.svg';
          if (!settings.territories) {
            if (!settings.hideCountries) {
              settings.hideCountries = [];
            }
            settings.hideCountries.push("vi");
            settings.hideCountries.push("gu");
            settings.hideCountries.push("mp");
            settings.hideCountries.push("as");
            settings.hideCountries.push("pr");

          }
        } else if (settings.map == "custom") {
          var url = settings.customMap;
          if (!settings.territories) {
            if (!settings.hideCountries) {
              settings.hideCountries = [];
            }
            settings.hideCountries.push("vi");
            settings.hideCountries.push("gu");
            settings.hideCountries.push("mp");
            settings.hideCountries.push("as");
            settings.hideCountries.push("pr");
          }
        }
        //hack for RequireJS/Bower
        if (typeof(require) !== 'undefined' && settings.map != "custom") {
          var oriurl = file;
          url = require.toUrl('cfgeoMap').replace('jquery.cfgeoMap', CFGP_MAP.maps + '/' + file);
        } else if (!settings.customMap) {
          url = CFGP_MAP.maps + '/' + file;
        } else {
          url = settings.customMap;
        }

        that.load(url, null, function(e) {
          $("svg", that).attr({
            height: '100%',
            width: '100%'
          }).css({
            background: settings.backgroundColor,
            height: '100%',
            width: '100%'
          });
          var hiddens = settings.hideCountries;
          for (i in hiddens) {
            $("path#" + hiddens[i]+", rect#" + hiddens[i]+", svg path#" + hiddens[i].toUpperCase(), that).remove();
          };
          if (settings.labels && (settings.map == "usa" || settings.map == "custom")) {
            var paths = document.querySelectorAll("path");
           
            //Define the states too small to hold a label
            var toosmall = ['nh', 'vt', 'ma', 'ri', 'ct', 'nj', 'de', 'md', 'dc'];
            
            //Hawaii uses the primary color
            addText(document.getElementById("hi"), "HI", 300, 575, settings.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            
            //The rest are the same
            addText(document.getElementById("ak"), "AK", 110, 500, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("fl"), "FL", 755, 511, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("sc"), "SC", 752, 380, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ga"), "GA", 700, 405, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("al"), "AL", 645, 415, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("nc"), "NC", 767, 332, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("tn"), "TN", 647, 345, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("me"), "ME", 885, 86, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ny"), "NY", 807, 155, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("pa"), "PA", 770, 211, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("wv"), "WV", 735, 275, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ky"), "KY", 665, 305, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("oh"), "OH", 694, 236, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("mi"), "MI", 650, 175, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("wy"), "WY", 293, 181, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("mt"), "MT", 273, 86, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("id"), "ID", 183, 151, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("wa"), "WA", 116, 48, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("tx"), "TY", 404, 452, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ca"), "CA", 65, 280, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("az"), "AZ", 193, 364, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("nv"), "NV", 132, 251, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ut"), "UT", 216, 248, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("co"), "CO", 317, 272, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("nm"), "NM", 297, 373, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("or"), "OR", 96, 118, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("nd"), "ND", 408, 92, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("sd"), "SD", 408, 163, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ne"), "NE", 408, 223, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ia"), "IA", 523, 214, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ms"), "MS", 588, 418, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("in"), "IN", 644, 255, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("il"), "IL", 590, 260, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("mn"), "MN", 495, 116, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("wi"), "WI", 574, 151, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("mo"), "MO", 535, 294, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ar"), "AR", 537, 375, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ok"), "OK", 432, 361, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ks"), "KS", 439, 291, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("la"), "LA", 538, 456, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
            addText(document.getElementById("ca"), "CA", 778, 282, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
  
            var init = 200;
            for (var i = 0; i < toosmall.length; i++) {
              var state = toosmall[i];
              addLabelRectangle(state, init, (settings.labelAttributes.backgroundColor || settings.color), settings.borderColor, settings.labelAttributes.color,settings.labelAttributes.font,settings.labelAttributes.fontSize);
              init = init + 25;
            }
          }

          $("svg path, svg rect", that).css({
            fill: settings.color,
            stroke: settings.border,
            strokeWidth: settings.borderWidth
          });
          var ics = settings.individualCountrySettings;
          for (i in ics) {
            $("svg path#" + ics[i].name+", svg rect#" + ics[i].name+", svg path#" + ics[i].name.toUpperCase(), that).css({
              fill: ics[i].color || settings.colorSelected,
              stroke: ics[i].border || settings.border
            });
			
			if( typeof ics[i].label != "undefined" ) {
				$("svg path#" + ics[i].name+", svg rect#" + ics[i].name+", svg path#" + ics[i].name.toUpperCase(), that).attr({
					'name':ics[i].label
				});
			}
          }
          $("svg", that).on("click touchstart", function(e) {
            var $target = $(e.target),
				country_code = $target.attr("id"),
				label = $target.attr("data-label");
            settings.click({
				'country_code':country_code,
				'label':label,
				'self':self
			}, e);
          });

          $("svg", that).on("mouseover", function(e) {
            var $target = $(e.target),
				country_code = $target.attr("id"),
				label = $target.attr("data-label");
            settings.hover({
				'country_code':country_code,
				'label':label,
				'self':self
			}, e);
          });

          $("svg", that).on("mouseout ontouchmove", function(e) {
			var $target = $(e.target),
				country_code = $target.attr("id"),
				label = $target.attr("data-label");
            settings.blur({
				'country_code':country_code,
				'label':label,
				'self':self
			}, e);
          });

          $("svg path, svg rect, svg text", that).on("mouseover", function(e) {
            if (ics && ics.length) {
              var icss = $.map(ics, function(o) {
                return o["name"];
              });
            }
            var country = $(e.target).attr("id");
            if (settings.disableCountries && settings.disableCountries.indexOf(country) == -1) {
              if (ics && icss.indexOf(country) > -1) {
                for (i in ics) {
                  if (ics[i].name == country) {
                    $("svg path#" + ics[i].name+", svg rect#" + ics[i].name+", svg path#" + ics[i].name.toUpperCase(), that).css({
                      fill: ics[i].hoverColor || settings.hoverColor,
                      stroke: ics[i].hoverBorder || settings.hoverBorder
                    });

                  }
                }
              } else {
                $("path#" + country+", rect#" + country+", path#" + country.toUpperCase(), that).css({
                  "fill": settings.hoverColor,
                  "stroke": settings.hoverBorder
                });
              }

              if (settings.littleRedBook) {
                if (country == "cn" || country == "tw" || country == "hk" || country == "mc") {
                  $("path#tw, path#cn, path#hk, path#mc, path#TW, path#CN, path#HK, path#MC").css({
                    "fill": settings.hoverColor,
                    "stroke": settings.hoverBorder
                  });
                }
              }
            }
          });

          $("svg path, svg text, svg rect", that).on("mouseout", function(e) {
            var country = $(e.target).attr("id");
            $("path#" + country+", rect#" + country+", path#" + country.toUpperCase(), that).css({
              "fill": settings.color,
              "stroke": settings.border
            });
            for (i in ics) {
              $("svg rect#" + ics[i].name+", svg path#" + ics[i].name+", path#" + ics[i].name.toUpperCase(), that).css({
                fill: ics[i].color || settings.colorSelected,
                stroke: ics[i].border || settings.border
              });
            }

            if (settings.littleRedBook) {
              if ( ["cn", "tw", "hk", "mc", "CN", "TW", "HK", "MC"].indexOf(country) > -1 ) {
                $('path#tw, path#cn, path#hk, path#mc, path#TW, path#CN, path#HK, path#MC', that).css({
                  "fill": settings.color,
                  "stroke": settings.border
                });
              }
            }
          });

          

          if (typeof callback === "function") callback();

        });
        return that;
      }

    var setCountrySettings = function(options) {
        if (options.color) {
          $("svg path#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase(), self).css({
            fill: options.color
          });
          $("svg path#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase(), self).on("mouseout", function() {
            $(this).css({
              fill: options.color
            });
          });
        }
        if (options.border) {
          $("svg path#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase(), self).css({
            stroke: options.border
          });
          $("svg path#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase(), self).on("mouseout", function() {
            $(this).css({
              stroke: options.border
            });
          });
        }
        if (options.borderWidth) {
          $("svg path#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase(), self).css({
            strokeWidth: options.borderWidth
          });
        }
        if (options.hoverColor) {
          $("svg path#" + options.name + ", svg text#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase(), self).on("mouseover", function(e) {
            $("svg path#" + options.name+", svg path#" + options.name.toUpperCase(), self).css({
              fill: options.hoverColor
            });
            if (options.hoverBorder) {
              $("svg path#" + options.name + ", svg text#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase()).css({
                stroke: options.hoverBorder
              });
            }
          });
          $("svg path#" + options.name + ", svg text#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase(), self).on("mouseout", function(e) {
            $("svg path#" + options.name+", svg path#" + options.name.toUpperCase(), self).css({
              fill: options.color
            });
            if (options.hoverBorder) {
              $("svg path#" + options.name + ", svg text#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase(), self).css({
                stroke: options.border
              });
            }
          });
        }
        if (options.click) {
          $("svg path#" + options.name + ", svg text#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase(), self).on("click", function(e) {
            e.stopPropagation()
            lastclicked = $(e.target).attr("id");
            options.click(lastclicked);
          });
        }
        if (options.hover) {
          $("svg path#" + options.name + ", svg text#" + options.name+", svg rect#" + options.name+", svg path#" + options.name.toUpperCase(), self).on("mouseover", function(e) {
            e.stopPropagation()
            lastclicked = $(e.target).attr("id");
            options.hover(lastclicked);
          });
        }

      };

    switch (action) {
    case "create":
      return create(options, callback);
      break;
    case "destroy":
      return destroy(options, callback);
      break;
    case "setCountry":
      return setCountrySettings(options);
      break;
    default:
      return create(options, callback);
    }
  };
}(jQuery||window.jQuery||Zepto||window.Zepto));