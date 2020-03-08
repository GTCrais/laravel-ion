<template>
	<ul class="pagination justify-content-center" role="navigation">

		<li v-if="onFirstPage()" class="page-item disabled" aria-disabled="true" aria-label="Previous">
			<span class="page-link" aria-hidden="true">
				<span class="fa fa-angle-left text-lighter"></span>
			</span>
		</li>

		<li v-else class="page-item">
			<a class="page-link" @click.prevent="goToPage(previousPage())" href="#" rel="prev" aria-label="Previous">
				<span class="fa fa-angle-left"></span>
			</a>
		</li>


		<li v-for="page in finalPages"
			class="page-item"
			:class="getListClass(page)"
			:aria-disabled="(page == '...') ? 'true' : 'false'"
			:aria-current="(page == paginator.current_page) ? 'page' : 'false'"
		>

			<span v-if="(page == '...')" class="page-link">{{ page }}</span>

			<span v-if="(page == paginator.current_page)" class="page-link cursor-default">{{ page }}</span>

			<a v-if="(page != paginator.current_page && page != '...')" class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
		</li>


		<li v-if="hasMorePages()" class="page-item">
			<a class="page-link" @click.prevent="goToPage(nextPage())" href="#" rel="next" aria-label="Next">
				<span class="fa fa-angle-right"></span>
			</a>
		</li>

		<li v-else class="page-item disabled" aria-disabled="true" aria-label="Next">
			<span class="page-link" aria-hidden="true">
				<span class="fa fa-angle-right text-lighter"></span>
			</span>
		</li>

	</ul>
</template>

<script type="text/babel">
	export default {
		props: {
			paginator: { default: null },
			sidePageCount: { default: 2 },
			adjacentPageCount: { default: 1 }
		},

		data: function() {
			return {

			}
		},

		mounted: function() {
			/*console.log(this.leftPages);
			console.log(this.leftSeparator);
			console.log(this.middlePages);
			console.log(this.rightSeparator);
			console.log(this.rightPages);
			console.log('------------------------');
			console.log(this.finalPages);*/
		},

		methods: {
			onFirstPage: function() {
				return this.paginator.current_page == 1;
			},

			hasMorePages: function() {
				return this.paginator.current_page < this.paginator.last_page;
			},

			previousPage: function() {
				return this.paginator.current_page > 1 ?
					this.paginator.current_page - 1 :
					1;
			},

			nextPage: function() {
				return this.paginator.current_page < this.paginator.last_page ?
					this.paginator.current_page + 1 :
					this.paginator.last_page;
			},

			goToPage: function(page) {
				this.$emit('go-to-page', page);
			},

			getListClass: function(page) {
				let listClass = [];

				if (page == '...') {
					listClass.push('disabled');
				}

				if (page == this.paginator.current_page) {
					listClass.push('active');
				}

				return listClass.join(" ");
			}
		},

		computed: {
			leftPages: function() {
				let leftPages = [];
				let leftPageCount = this.paginator.last_page >= this.sidePageCount ?
					this.sidePageCount :
					this.paginator.last_page

				for (let page = 1; page <= leftPageCount; page++) {
					leftPages.push(page);
				}

				return leftPages;
			},

			rightPages: function() {
				if (this.paginator.last_page > this.sidePageCount) {
					let rightPages = [];
					let rightPageCount = (this.paginator.last_page - this.sidePageCount >= this.sidePageCount) ?
						this.sidePageCount :
						(this.paginator.last_page - this.sidePageCount);
					let leftMostRightPage = this.paginator.last_page - rightPageCount + 1;

					for (let i = leftMostRightPage; i <= this.paginator.last_page; i++) {
						rightPages.push(i);
					}

					return rightPages;
				}

				return [];
			},

			middlePages: function() {
				if (this.paginator.last_page > (this.sidePageCount * 2)) {
					let middlePages = [];
					let leftMostPage;
					let rightMostPage;

					/*** Left Most Page ***/

					if (this.paginator.current_page <= this.sidePageCount) {

						leftMostPage = this.sidePageCount + 1;

					} else if (this.paginator.current_page >= (this.paginator.last_page - this.sidePageCount + 1 - this.adjacentPageCount)) {

						let maxLeftMostPage = this.paginator.current_page - this.adjacentPageCount;
						leftMostPage = maxLeftMostPage > this.sidePageCount ?
							maxLeftMostPage :
							this.sidePageCount + 1;

					} else {

						let maxLeftMostPage = this.paginator.current_page - this.adjacentPageCount;
						leftMostPage = maxLeftMostPage > this.sidePageCount ?
							maxLeftMostPage :
							this.sidePageCount + 1;

					}

					/*** Right Most Page ***/

					if (this.paginator.current_page >= (this.paginator.last_page - this.sidePageCount + 1)) {

						//console.error("here 1");

						rightMostPage = this.paginator.last_page - this.sidePageCount;

					} else if (this.paginator.current_page <= (this.sidePageCount + this.adjacentPageCount)) {

						//console.error("here 2");

						let maxRightMostPage = this.paginator.current_page + this.adjacentPageCount;
						rightMostPage = maxRightMostPage <= (this.paginator.last_page - this.sidePageCount) ?
							maxRightMostPage :
							(this.paginator.last_page - this.sidePageCount);

					} else {

						//console.error("here 3");

						let maxRightMostPage = this.paginator.current_page + this.adjacentPageCount;
						rightMostPage = maxRightMostPage <= (this.paginator.last_page - this.sidePageCount) ?
							maxRightMostPage :
							(this.paginator.last_page - this.sidePageCount);

					}

					/*** Create Middle Pages ***/

					for (let i = leftMostPage; i <= rightMostPage; i++) {
						middlePages.push(i);
					}

					return middlePages;
				}

				return [];
			},

			leftSeparator: function() {
				if (
					this.middlePages.length &&
					this.middlePages[0] > (this.leftPages.slice(-1)[0] + 1)
				) {
					return '...';
				}

				if (
					!this.middlePages.length &&
					this.rightPages.length
				) {
					return '...';
				}

				return null;
			},

			rightSeparator: function() {
				if (
					this.middlePages.length &&
					this.rightPages.length &&
					this.rightPages[0] > (this.middlePages.slice(-1)[0] + 1)
				) {
					return '...';
				}

				return null;
			},

			finalPages: function() {
				let finalPages = this.leftPages;

				if (this.leftSeparator) {
					finalPages.push(this.leftSeparator);
				}

				if (this.middlePages.length) {
					finalPages.push(...this.middlePages);
				}

				if (this.rightSeparator) {
					finalPages.push(this.rightSeparator);
				}

				if (this.rightPages.length) {
					finalPages.push(...this.rightPages);
				}

				return finalPages;
			}
		},
	}
</script>