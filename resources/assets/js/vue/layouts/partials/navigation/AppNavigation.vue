<template>
	<div id="sidebar-wrapper" data-simplebar data-simplebar-auto-hide="true">
		<div class="brand-logo">

			<router-link :to="{name: 'home'}">
				<img src="/vendor/ion/img/logo.svg" class="logo-icon" alt="logo icon">
			</router-link>

		</div>

		<ul class="sidebar-menu do-nicescrol"
			ref="sidebarMenu"
		>
			<li class="sidebar-header"></li>

			<navigation-list-item
				v-for="(item, index) in navigationEntities"
				v-if="userCanSee(item.uid)"
				:key="'nav-key-' + index"
				:item="item">
			</navigation-list-item>

		</ul>
	</div>
</template>

<script type="text/babel">
	export default {
		props: [

		],

		data: function() {
			return {
				navigationEntities: this.$store.getters.navigation,
				entityConfigs: this.$store.getters.entityConfigs,
				user: this.$store.getters.user
			}
		},

		mounted: function() {
			this.setupSidebarMenu();
		},

		methods: {
			userCanSee: function(uid) {
				let entityConfig = this.entityConfigs[uid];

				if (!entityConfig.viewableForRoles) {
					return true;
				}

				if (_.includes(entityConfig.viewableForRoles, this.user.role)) {
					return true;
				}

				return false;
			},

			setupSidebarMenu: function() {
				const menu = this.$refs.sidebarMenu;
				const animationSpeed = 300;
				const subMenuSelector = '.sidebar-submenu';

				$(menu).on('click', 'li a', function(e) {
					const $this = $(this);
					let checkElement = $this.next();

					if (checkElement.is(subMenuSelector) && checkElement.is(':visible')) {
						checkElement.slideUp(animationSpeed, function() {
							checkElement.removeClass('menu-open');
						});
						checkElement.parent("li").removeClass("active");
					}

					// If the menu is not visible
					else if ((checkElement.is(subMenuSelector)) && (!checkElement.is(':visible'))) {

						// Get the parent menu
						let parent = $this.parents('ul').first();

						// Close all open menus within the parent
						let ul = parent.find('ul:visible').slideUp(animationSpeed);

						// Remove the menu-open class from the parent
						ul.removeClass('menu-open');

						// Get the parent li
						let parent_li = $this.parent("li");

						// Open the target menu and add the menu-open class
						checkElement.slideDown(animationSpeed, function() {

							//Add the class active to the parent li
							checkElement.addClass('menu-open');
							parent.find('li.active').removeClass('active');
							parent_li.addClass('active');
						});
					}

					// Ff this isn't a link, prevent the page from being redirected
					if (checkElement.is(subMenuSelector)) {
						e.preventDefault();
					}
				});
			}
		},

		computed: {

		}
	}
</script>