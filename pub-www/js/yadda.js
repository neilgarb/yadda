Function.prototype.delegate = function (o) {
	var fn = this;
	return function() {
		if (arguments.length > 0) {
			return fn.apply(o, arguments);
		} else {
			return fn.call(o);
		}
	}
}

var YaddaSearch = function (domId, params) {
	var _domId = domId;
	var _el = $('#'+_domId);
	
	var _params = params;
	var _page = 3;
	var _count = 15;
	
	var _fetching = false;
	var _done = false;
	
	return {
		endFetch: function () {
			_fetching = false;
		},
		fetchDone: function (d) {
			_page ++;
			if (d.length == 0) {
				_done = true;
				return;
			}
			for (var i in d) {
				_el.append($(d[i]));
			}
			this.endFetch();
		},
		fetch: function () {
			_fetching = true;
			var data = { page: _page, count: _count };
			for (var i in _params) {
				if (_params[i] != null) {
					data[i] = _params[i];
				}
			}
			$.ajax('/ajax/deal/search', {
				data: data,
				success: this.fetchDone.delegate(this)
			});
		},
		scroll: function () {
			var windowTop = $(window).scrollTop();
			var windowBottom = windowTop + $(window).height();
			var listTop = _el.offset().top;
			var listBottom = listTop + _el.height();
			if (listBottom <= windowBottom) {
				if (!_fetching && !_done) {
					this.fetch();
				}
			}
		},
		init: function () {
			if (_el.length == 0) {
				return;
			}
			$(window).scroll(this.scroll.delegate(this));
		}
	}
}

var YaddaFixed = function (domId) {
	var _el = $('#'+domId);
	var _top = _el.offset().top;
	return {
		scroll: function (e) {
			if ($(window).scrollTop() > _top - 5 && ($('body').height() - $(window).height() > _top) && $(window).height() > _el.height()) {
				_el.addClass('fixed');
				var spaceLeft = $('#footer').offset().top - $(window).scrollTop();
				var diff = spaceLeft - _el.height();
				var top = 5;
				if (diff < 0) {
					top += diff;
				}
				_el.css({ top: top });
			} else {
				_el.removeClass('fixed');
			}
		},
		resize: function (e) {
			if ($(window).height() < _el.height()) {
				_el.removeClass('fixed');
			}
			this.scroll();
		},
		init: function () {
			$(window).scroll(this.scroll.delegate(this));
			$(window).resize(this.resize.delegate(this));
		}
	}
}

var Yadda  = function () {
	return {
		closeFlash: function (e) {
			if (e) { 
				e.preventDefault();
			}
			var height = $('#flash').height();
			$('#flash').animate({ opacity:0, top:-height }, 'fast', function () {
				$('#flash').remove();
			});
		},
		init: function () {
			// flash messages
			setTimeout(this.closeFlash.delegate(this), 5000);
			
			// submit buttons
			$('input[type=submit]').each(function () {
				$(this).parents('form').submit(function () {
					var submits = $(this).find('input[type=submit]'); 
					submits.val('Submitting...');
					submits.attr('disabled', 'disabled');
				});
			});
			
			// external clicks
			$('a.external').click(function (e) {
				e.preventDefault();
				window.open($(this).attr('href'));
			});
		}
	}
}

var YaddaMap = function (domId) {
	var _domId = domId;
	var _el = $('#'+_domId);
	
	var _map;
	var _markers = [];
	var _clusterer;
	
	var _boundsTimeout;
	
	var _curIds = [];
	
	var _cstyles = [{
		url: '/img/map-marker-cluster.png',
		width: 40,
		height: 40
	}];
	
	var _mimage = new google.maps.MarkerImage(
		'/img/map-marker.png',
		new google.maps.Size(21, 29),
		new google.maps.Point(0, 0),
		new google.maps.Point(11, 29)
	);
	
	var _mshadow = new google.maps.MarkerImage(
		'/img/map-marker-shadow.png',
		new google.maps.Size(39,29),
		new google.maps.Point(0,0),
		new google.maps.Point(11,29)
	);
	
	var _mshape = {
		coord: [19,0,20,1,20,2,20,3,20,4,20,5,20,6,20,7,20,8,20,9,20,10,20,11,20,12,20,13,20,14,20,15,20,16,20,17,20,18,20,19,19,20,18,21,17,22,16,23,15,24,14,25,13,26,12,27,11,28,10,28,8,27,7,26,6,25,5,24,4,23,3,22,2,21,1,20,0,19,0,18,0,17,0,16,0,15,0,14,0,13,0,12,0,11,0,10,0,9,0,8,0,7,0,6,0,5,0,4,0,3,0,2,0,1,8,0,19,0],
		type: 'poly'
	};
	
	var _loader;
	var _resultCount;
	
	var _drawer;
	
	return {
		setCenterFromUserAgent: function () {
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function (p) {
					_map.setCenter(new google.maps.LatLng(p.coords.latitude, p.coords.longitude));
					_map.setZoom(10);
				});
			} else if (google.gears) {
				var geo = google.gears.factory.create('beta.geolocation');
				geo.getCurrentPosition(function (p) {
					_map.setCenter(new google.maps.LatLng(p.coords.latitude, p.coords.longitude));
					_map.setZoom(10);
				});
			}
		},
		boundsHandler: function () {
			this.setLoading(true);
			if (_boundsTimeout) {
				clearTimeout(_boundsTimeout);
			}
			_boundsTimeout = setTimeout(this.boundsHandlerComplete.delegate(this), 1000);
		},
		boundsHandlerComplete: function () {
			// get bounding coordinates
			var bounds = _map.getBounds();
			var ne = bounds.getNorthEast();
			var sw = bounds.getSouthWest();
			var points = [
				ne.lat(),
				ne.lng(),
				sw.lat(),
				sw.lng()
			];
			$.ajax('/ajax/map/findDealsByMapBounds', {
				data: { bounds: points.join(',') },
				success: this.fetchDealsHandler.delegate(this) 
			});
		},
		sameIds: function (d) {
			var tIds = [];
			for (var i in d) {
				tIds.push(d[i].id);
			}
			var rtn = this.compareArrays(_curIds, tIds);
			_curIds = tIds;
			return rtn;
		},
		fetchDealsHandler: function (d) {
			// if these are the same markers, then we don't need to do anything
			if (this.sameIds(d)) {
				this.setLoading(false);
				return;
			}
			for (var m in _markers) {
				_markers[m].setMap(null);
			}
			_markers = [];
			for (var i in d) {
				var deal = d[i];
				var marker = new google.maps.Marker({
					position: new google.maps.LatLng(deal.lat, deal['long']),
					title: deal.title,
					deal: deal,
					icon: _mimage,
					shadow: _mshadow,
					shape: _mshape
				});
				_markers.push(marker);
				var ym = this;
				google.maps.event.addListener(marker, 'click', function () {
					ym.markerHandler(this);
				});
			}
			if (_clusterer) {
				_clusterer.clearMarkers();
			}
			_clusterer = new MarkerClusterer(_map, _markers, {
				styles: _cstyles,
				gridSize: 40,
				zoomOnClick: false
			});
			google.maps.event.addListener(_clusterer, 'clusterclick', this.clusterHandler.delegate(this));
			this.setLoading(false);
			this.setShowing(d.length);
		},
		markerHandler: function (m) {
			var deals = [m.deal];
			this.showDeals(deals);
		},
		clusterHandler: function (c) {
			var deals = [];
			var markers = c.getMarkers();
			for (var i in markers) {
				deals.push(markers[i].deal);
			}
			this.showDeals(deals);
		},
		showDeals: function (deals) {
			if (!_drawer) {
				_drawer = $('<div id="drawer"><div class="deals"></div></div>');
				$('body').append(_drawer);
				var height = $(window).height() - 170;
				_drawer.height(height);
				_drawer.css({ right: -_drawer.width(), opacity:0}).animate({ right: 0, opacity:1 }, 'fast');
			} else {
				_drawer.css({ right: -10 }).animate({ right:0 }, 'fast');
			}
			var div = _drawer.find('div.deals');
			div.html('');
			for (var i in deals) {
				div.append($(deals[i].html));
			}
			
		},
		htmlHandler: function (d) {
			console.log(d);
			this.setLoading(false);
		},
		jumpHandler: function () {
			var coords = eval($('#region-select').val());
			if (coords != undefined) {
				_map.setCenter(new google.maps.LatLng(coords[0], coords[1]));
				_map.setZoom(10);
			}
		},
		resizeHandler: function (e) {
			var height = $(window).height() - 170;
			_el.width($(window).width());
			_el.height(height);
			if (_drawer) {
				_drawer.height(height);
			}
		},
		setLoading: function (loading) {
			if (!_loader) {
				_loader = $('<img src="/img/loader-eeeeee.gif" alt="[Loading...]" id="loader" />');
				$('body').append(_loader);
			}
			loading ? _loader.show() : _loader.hide();
			if (_resultCount) {
				loading ? _resultCount.hide() : _resultCount.show();
			}
		},
		setShowing: function (count) {
			if (!_resultCount) {
				_resultCount = $('<span id="result-count"></span>');
				$('body').append(_resultCount);
			}
			_resultCount.html('Showing '+count+' deal'+(count == 1 ? '' : 's')+'.');
		},
		init: function () {
			
			// create the map
			var options = {
				zoom: 5,
				center: new google.maps.LatLng(-29, 24),
				mapTypeId: google.maps.MapTypeId.ROADMAP
//				disableDefaultUI: true,
//				zoomControl: true,
//				zoomControlOptions: {
//					style: google.maps.ZoomControlStyle.SMALL,
//					position: google.maps.ControlPosition.LEFT_CENTER
//				}
			};
			_map = new google.maps.Map(_el.get(0), options);
			
			// set center from user agent location
			this.setCenterFromUserAgent();
			
			// listen for bounds change
			google.maps.event.addListener(_map, 'bounds_changed', this.boundsHandler.delegate(this));
			
			// configure region jump
			$('#region-select-go').click(this.jumpHandler.delegate(this));
			
			// configure window resize
			$(window).resize(this.resizeHandler.delegate(this));
			this.resizeHandler();
		},
		compareArrays: function (a1, a2) {
			if (a1.length != a2.length) return false;
			for (var i = 0; i < a2.length; i++) {
				if (a1[i] !== a2[i]) return false;
			}
			return true;
		}
	};
}

$(function () {
	(new Yadda()).init();
});