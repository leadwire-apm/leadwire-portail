(function(angular) {
    angular
        .module('leadwireApp')
        .value('paginationOptions', {
            itemsPerPage: 5,
            start: 0,
            end: 5,
            currentPage: 1,
            items: []
        })
        .factory('Paginator', function(paginationOptions) {
            function Paginator(options) {
                var defaults = angular.copy(paginationOptions);
                angular.extend(this, defaults, options);
                this.end = this.itemsPerPage;
            }

            Paginator.prototype.nextPage = function() {
                if (this.items.length && this.end <= this.items.length - 1) {
                    this.start += this.itemsPerPage;
                    this.end += this.itemsPerPage;
                    this.currentPage++;
                }
            };

            Paginator.prototype.prevPage = function() {
                if (
                    this.items.length &&
                    this.start <= this.items.length &&
                    this.start > 0
                ) {
                    this.start -= this.itemsPerPage;
                    this.end -= this.itemsPerPage;
                    this.currentPage--;
                }
            };

            Paginator.prototype.goToPage = function(index) {
                this.start = this.itemsPerPage * (index - 1);
                this.end = this.itemsPerPage * index;
                this.currentPage = index;
            };

            Paginator.prototype.getPageNumber = function() {
                var number = this.items.length / this.itemsPerPage;
                try {
                    return new Array(number + 1);
                } catch (e) {
                    return new Array(Math.ceil(number + 1));
                }
            };

            Paginator.prototype.isCurrent = function(index) {
                return this.currentPage === index;
            };

            Paginator.create = function(options) {
                return new Paginator(options);
            };

            return Paginator;
        });
})(window.angular);
