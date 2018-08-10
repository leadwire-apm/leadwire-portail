'use strict';

var Form = function(parameters) {
	var _params = parameters;
	var _object = {
		add_field: function(container, index) {
			var html = container.data('prototype');
			html = html.replaceAll('__name__', index);
			$('<li>' + html + '<a href="" class="remove-field fa fa-minus ml-1"></a></li>').insertBefore(container.children('a').get(0));
		},
		remove_field: function(element) {
			var newIndex = 0;
			var parent = element.parent();

			element.remove();
			parent.children('li').each(function(element) {
				console.log(element);
				console.log(element.attr('id'));
			});
		},
		bind: function() {
			$(document).on('click', '.add-field', function(event) {
				event.preventDefault();
				var container = $(this).parent();
				_object.add_field(container, container.children('li').length)
			});
			$(document).on('click', '.remove-field', function(event) {
				event.preventDefault();
				_object.remove_field($(this).parent());
			});
		},
		init: function() {
            _object.bind();
		}
	};

	return {
		init: function() {
		    _object.init();
		}
	};
}

String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};