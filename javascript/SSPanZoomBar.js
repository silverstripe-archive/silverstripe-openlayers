/* Copyright (c) 2006-2008 MetaCarta, Inc., published under the Clear BSD
 * license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
 * full text of the license. */


/**
 * @requires OpenLayers/Control/PanZoom.js
 */

/**
 * Class: OpenLayers.Control.PanZoomBar
 * The PanZoomBar is a visible control composed of a
 * <OpenLayers.Control.PanPanel> and a <OpenLayers.Control.ZoomBar>. 
 * By default it is displayed in the upper left corner of the map as 4
 * directional arrows above a vertical slider.
 *
 * Inherits from:
 *  - <OpenLayers.Control.PanZoom>
 */
OpenLayers.Control.SSPanZoomBar = OpenLayers.Class(OpenLayers.Control.PanZoom, {

    /** 
     * APIProperty: zoomStopWidth
     */
    zoomStopWidth: 27,

    /** 
     * APIProperty: zoomStopHeight
     */
    zoomStopHeight: 14,

    /** 
     * Property: slider
     */
    slider: null,

    /** 
     * Property: sliderEvents
     * {<OpenLayers.Events>}
     */
    sliderEvents: null,

    /** 
     * Property: zoomBarDiv
     * {DOMElement}
     */
    zoomBarDiv: null,

    /** 
     * Property: divEvents
     * {<OpenLayers.Events>}
     */
    divEvents: null,

    /** 
     * APIProperty: zoomWorldIcon
     * {Boolean}
     */
    zoomWorldIcon: false,

    /**
     * Constructor: OpenLayers.Control.PanZoomBar
     */ 
    initialize: function() {
        OpenLayers.Control.PanZoom.prototype.initialize.apply(this, arguments);
    },

    /**
     * APIMethod: destroy
     */
    destroy: function() {

        this._removeZoomBar();

        this.map.events.un({
            "changebaselayer": this.redraw,
            scope: this
        });

        OpenLayers.Control.PanZoom.prototype.destroy.apply(this, arguments);
    },
    
    /**
     * Method: setMap
     * 
     * Parameters:
     * map - {<OpenLayers.Map>} 
     */
    setMap: function(map) {
        OpenLayers.Control.PanZoom.prototype.setMap.apply(this, arguments);
        this.map.events.register("changebaselayer", this, this.redraw);
    },

    /** 
     * Method: redraw
     * clear the div and start over.
     */
    redraw: function() {
        if (this.div != null) {
            this.removeButtons();
            this._removeZoomBar();
        }  
        this.draw();
    },
    
    /**
    * Method: draw 
    *
    * Parameters:
    * px - {<OpenLayers.Pixel>} 
    */
    draw: function(px) {
        // initialize our internal div
        OpenLayers.Control.prototype.draw.apply(this, arguments);
        px = this.position.clone();

        // place the controls
        this.buttons = [];

        var sz = new OpenLayers.Size(27,16);
		var szLR = new OpenLayers.Size(16,26);
		var szC = new OpenLayers.Size(27,26);
		var szH = new OpenLayers.Size(29,24);
		var szZ = new OpenLayers.Size(27,24);
        var centered = new OpenLayers.Pixel(px.x+szH.w/2, px.y);
        var wposition = szH.w;

        if (this.zoomWorldIcon) {
            centered = new OpenLayers.Pixel(px.x+sz.w, px.y);
        }

        this._addSSButton("panup", "pan_up.png", px.add( 16, 0), sz);
        px.y = centered.y+sz.h;
        this._addSSButton("panleft", "pan_left.png", px, szLR);
        if (this.zoomWorldIcon) {
            this._addSSButton("zoomworld", "zoom-world-mini.png", px.add(sz.w, 0), sz);
            
            wposition *= 2;
        }
		this._addSSButton("pancenter", "pan_centre.png", px.add(16, 0), szC);
        this._addSSButton("panright", "pan_right.png", px.add(wposition + 14, 0), szLR);
        this._addSSButton("pandown", "pan_down.png", centered.add(1.5, 42), sz);
        this._addSSButton("zoomin", "zoom_plus.png", centered.add(2, szZ.h*3+5), szZ);
        centered = this._addZoomBar(centered.add(2, sz.h*4 + 5));
        this._addSSButton("zoomout", "zoom_minus.png", centered, szZ);
        return this.div;
    },

	_addSSButton:function(id, img, xy, sz) {
        var imgLocation = "openlayers/images/controls/" + img;
        var btn = OpenLayers.Util.createAlphaImageDiv(
                                    this.id + "_" + id, 
                                    xy, sz, imgLocation, "absolute");

        //we want to add the outer div
        this.div.appendChild(btn);

        OpenLayers.Event.observe(btn, "mousedown", 
            OpenLayers.Function.bindAsEventListener(this.buttonDown, btn));
        OpenLayers.Event.observe(btn, "dblclick", 
            OpenLayers.Function.bindAsEventListener(this.doubleClick, btn));
        OpenLayers.Event.observe(btn, "click", 
            OpenLayers.Function.bindAsEventListener(this.doubleClick, btn));
        btn.action = id;
        btn.map = this.map;
    
        if(!this.slideRatio){
            var slideFactorPixels = this.slideFactor;
            var getSlideFactor = function() {
                return slideFactorPixels;
            };
        } else {
            var slideRatio = this.slideRatio;
            var getSlideFactor = function(dim) {
                return this.map.getSize()[dim] * slideRatio;
            };
        }

        btn.getSlideFactor = getSlideFactor;

        //we want to remember/reference the outer div
        this.buttons.push(btn);
        return btn;
    },

    /** 
    * Method: _addZoomBar
    * 
    * Parameters:
    * location - {<OpenLayers.Pixel>} where zoombar drawing is to start.
    */
    _addZoomBar:function(centered) {
        var imgLocation = "openlayers/images/controls/";
        centered.y = centered.y + 32;
        var id = this.id + "_" + this.map.id;
        var zoomsToEnd = this.map.getNumZoomLevels() - 1 - this.map.getZoom();
        var slider = OpenLayers.Util.createAlphaImageDiv(id,
                       centered.add(-1, zoomsToEnd * this.zoomStopHeight), 
                       new OpenLayers.Size(27,9), 
                       //imgLocation+"slider.png",
					   "openlayers/images/controls/zoom_bar.png",
                       "absolute");
        this.slider = slider;
        
        this.sliderEvents = new OpenLayers.Events(this, slider, null, true,
                                            {includeXY: true});
        this.sliderEvents.on({
            "mousedown": this.zoomBarDown,
            "mousemove": this.zoomBarDrag,
            "mouseup": this.zoomBarUp,
            "dblclick": this.doubleClick,
            "click": this.doubleClick
        });
        
        var sz = new OpenLayers.Size();
        sz.h = this.zoomStopHeight * this.map.getNumZoomLevels();
        sz.w = this.zoomStopWidth;
        var div = null;
        
        if (OpenLayers.Util.alphaHack()) {
            var id = this.id + "_" + this.map.id;
            div = OpenLayers.Util.createAlphaImageDiv(id, 
									  centered,
                                      new OpenLayers.Size(sz.w, 
                                              this.zoomStopHeight),
                                      imgLocation + "zoombar.png", 
                                      "absolute", null, "crop");
            div.style.height = sz.h + "px";
        } else {
            div = OpenLayers.Util.createDiv(
                        'OpenLayers_Control_PanZoomBar_Zoombar' + this.map.id,
                        centered,
                        sz,
                        imgLocation+"zoombar.png");
        }
        
        this.zoombarDiv = div;
        
        this.divEvents = new OpenLayers.Events(this, div, null, true, 
                                                {includeXY: true});
        this.divEvents.on({
            "mousedown": this.divClick,
            "mousemove": this.passEventToSlider,
            "dblclick": this.doubleClick,
            "click": this.doubleClick
        });
        
        this.div.appendChild(div);

        this.startTop = parseInt(div.style.top);
        this.div.appendChild(slider);

        this.map.events.register("zoomend", this, this.moveZoomBar);

        centered = centered.add(0, 
            this.zoomStopHeight * this.map.getNumZoomLevels());
        return centered; 
    },
    
    /**
     * Method: _removeZoomBar
     */
    _removeZoomBar: function() {
        this.sliderEvents.un({
            "mousedown": this.zoomBarDown,
            "mousemove": this.zoomBarDrag,
            "mouseup": this.zoomBarUp,
            "dblclick": this.doubleClick,
            "click": this.doubleClick
        });
        this.sliderEvents.destroy();

        this.divEvents.un({
            "mousedown": this.divClick,
            "mousemove": this.passEventToSlider,
            "dblclick": this.doubleClick,
            "click": this.doubleClick
        });
        this.divEvents.destroy();
        
        this.div.removeChild(this.zoombarDiv);
        this.zoombarDiv = null;
        this.div.removeChild(this.slider);
        this.slider = null;
        
        this.map.events.unregister("zoomend", this, this.moveZoomBar);
    },
    
    /**
     * Method: passEventToSlider
     * This function is used to pass events that happen on the div, or the map,
     * through to the slider, which then does its moving thing.
     *
     * Parameters:
     * evt - {<OpenLayers.Event>} 
     */
    passEventToSlider:function(evt) {
        this.sliderEvents.handleBrowserEvent(evt);
    },
    
    /**
     * Method: divClick
     * Picks up on clicks directly on the zoombar div
     *           and sets the zoom level appropriately.
     */
    divClick: function (evt) {
        if (!OpenLayers.Event.isLeftClick(evt)) {
            return;
        }
        var y = evt.xy.y;
        var top = OpenLayers.Util.pagePosition(evt.object)[1];
        var levels = (y - top)/this.zoomStopHeight;
        if(!this.map.fractionalZoom) {
            levels = Math.floor(levels);
        }    
        var zoom = (this.map.getNumZoomLevels() - 1) - levels; 
        zoom = Math.min(Math.max(zoom, 0), this.map.getNumZoomLevels() - 1);
        this.map.zoomTo(zoom);
        OpenLayers.Event.stop(evt);
    },
    
    /*
     * Method: zoomBarDown
     * event listener for clicks on the slider
     *
     * Parameters:
     * evt - {<OpenLayers.Event>} 
     */
    zoomBarDown:function(evt) {
        if (!OpenLayers.Event.isLeftClick(evt)) {
            return;
        }
        this.map.events.on({
            "mousemove": this.passEventToSlider,
            "mouseup": this.passEventToSlider,
            scope: this
        });
        this.mouseDragStart = evt.xy.clone();
        this.zoomStart = evt.xy.clone();
        this.div.style.cursor = "move";
        // reset the div offsets just in case the div moved
        this.zoombarDiv.offsets = null; 
        OpenLayers.Event.stop(evt);
    },
    
    /*
     * Method: zoomBarDrag
     * This is what happens when a click has occurred, and the client is
     * dragging.  Here we must ensure that the slider doesn't go beyond the
     * bottom/top of the zoombar div, as well as moving the slider to its new
     * visual location
     *
     * Parameters:
     * evt - {<OpenLayers.Event>} 
     */
    zoomBarDrag:function(evt) {
        if (this.mouseDragStart != null) {
            var deltaY = this.mouseDragStart.y - evt.xy.y;
            var offsets = OpenLayers.Util.pagePosition(this.zoombarDiv);
            if ((evt.clientY - offsets[1]) > 0 && 
                (evt.clientY - offsets[1]) < parseInt(this.zoombarDiv.style.height) - 2) {
                var newTop = parseInt(this.slider.style.top) - deltaY;
                this.slider.style.top = newTop+"px";
                this.mouseDragStart = evt.xy.clone();
            }
            OpenLayers.Event.stop(evt);
        }
    },
    
    /*
     * Method: zoomBarUp
     * Perform cleanup when a mouseup event is received -- discover new zoom
     * level and switch to it.
     *
     * Parameters:
     * evt - {<OpenLayers.Event>} 
     */
    zoomBarUp:function(evt) {
        if (!OpenLayers.Event.isLeftClick(evt)) {
            return;
        }
        if (this.zoomStart) {
            this.div.style.cursor="";
            this.map.events.un({
                "mouseup": this.passEventToSlider,
                "mousemove": this.passEventToSlider,
                scope: this
            });
            var deltaY = this.zoomStart.y - evt.xy.y;
            var zoomLevel = this.map.zoom;
            if (this.map.fractionalZoom) {
                zoomLevel += deltaY/this.zoomStopHeight;
                zoomLevel = Math.min(Math.max(zoomLevel, 0), 
                                     this.map.getNumZoomLevels() - 1);
            } else {
                zoomLevel += Math.round(deltaY/this.zoomStopHeight);
            }
            this.map.zoomTo(zoomLevel);
            this.moveZoomBar();
            this.mouseDragStart = null;
            OpenLayers.Event.stop(evt);
        }
    },
    
    /*
    * Method: moveZoomBar
    * Change the location of the slider to match the current zoom level.
    */
    moveZoomBar:function() {
        var newTop = 
            ((this.map.getNumZoomLevels()-1) - this.map.getZoom()) * 
            this.zoomStopHeight + this.startTop + 1;
        this.slider.style.top = newTop + "px";
    },    
    
    CLASS_NAME: "OpenLayers.Control.PanZoomBar"
});


// reset position of the control
OpenLayers.Control.PanZoom.X = 10;
OpenLayers.Control.PanZoom.Y = 14;
