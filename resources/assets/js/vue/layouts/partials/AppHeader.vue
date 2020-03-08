<template>
	<!--Start topbar header-->
	<header class="topbar-nav">
		<nav class="navbar navbar-expand fixed-top"
			 :class="{ 'bg-dark': navbarIsDark }"
		>
			<ul class="navbar-nav mr-auto align-items-center">
				<li class="nav-item">
					<a class="nav-link toggle-menu"
					   href="#"
					   @click.prevent="toggleMenu"
					>
						<i class="icon-menu menu-icon"></i>
					</a>
				</li>
			</ul>

			<ul class="navbar-nav align-items-center right-nav-link">

				<li class="nav-item">
					<a class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-toggle="dropdown" href="#">
						<span class="user-profile"><img src="/vendor/ion/img/avatar.png" class="img-circle" alt="user avatar"></span>
					</a>

					<ul class="dropdown-menu dropdown-menu-right">
						<li class="dropdown-item user-details">
							<router-link :to="linkToUser">
								<div class="media">
									<div class="avatar"><img class="align-self-start mr-3" src="/vendor/ion/img/avatar.png" alt="user avatar"></div>
									<div class="media-body">
										<h6 class="mt-2 user-title">{{ this.user.name }}</h6>
										<p class="user-subtitle">{{ this.user.email }}</p>
									</div>
								</div>
							</router-link>
						</li>

						<!--<li class="dropdown-item">
							<a href="#" @click.prevent="openSettings">
								<i class="icon-settings mr-2"></i> Settings
							</a>
						</li>-->

						<li v-if="user.role === 'superadmin'" class="dropdown-item">
							<a :href="migrateDatabaseUrl">
								<i class="fa fa-database mr-2"></i> Migrate DB
							</a>
						</li>

						<li v-if="user.role === 'superadmin'" class="dropdown-divider"></li>

						<li v-if="user.role === 'superadmin'" class="dropdown-item">
							<a :href="linkStorageUrl">
								<i class="fa fa-database mr-2"></i> Link Storage
							</a>
						</li>

						<li class="dropdown-divider"></li>

						<li class="dropdown-item">
							<a href="#" @click.prevent="logout">
								<i class="icon-power mr-2"></i> Logout
							</a>
						</li>
					</ul>
				</li>
			</ul>
		</nav>
	</header>
	<!--End topbar header-->
</template>

<script type="text/babel">
	export default {
		props: [

		],

		data: function() {
			return {
				user: this.$store.getters.user,
				navbarIsDark: false
			}
		},

		mounted: function() {
			this.setupTopHeaderStyleWhenScrolling();
		},

		methods: {
			logout: function() {
				this.$emit('logout');
			},

			openSettings: function() {
				eventHub.$emit('openSettings');
			},

			toggleMenu: function() {
				eventHub.$emit('menu-toggled');
			},

			setupTopHeaderStyleWhenScrolling: function() {
				const comp = this;

				$(window).on("scroll", function(){
					if ($(this).scrollTop() > 60) {
						comp.navbarIsDark = true;
					} else {
						comp.navbarIsDark = false;
					}
				});
			}
		},

		computed: {
			linkToUser: function() {
				return {
					'name': 'entityEdit',
					params: {
						'navigationEntity': 'user',
						'id': this.user.id
					}
				};
			},

			migrateDatabaseUrl: function() {
				return '/' + this.$store.getters.ionWebPath + '/system/migrate-database';
			},

			linkStorageUrl: function() {
				return '/' + this.$store.getters.ionWebPath + '/system/link-storage';
			}
		}
	}
</script>