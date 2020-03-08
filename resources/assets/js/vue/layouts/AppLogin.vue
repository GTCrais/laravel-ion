<template>

	<div class="card card-authentication1 mx-auto my-5 login-container">
		<div class="card-body">
			<div class="card-content p-2">

				<div class="text-center mb-4">
					<img class="logo" src="/vendor/ion/img/logo.svg" alt="logo icon">
				</div>

				<form method="post" action="/" @submit.prevent="submit">

					<div class="form-group">
						<label for="email" class="sr-only">Email</label>
						<div class="position-relative has-icon-right">
							<input
									v-model="email"
									type="text"
									id="email"
									class="form-control input-shadow"
									placeholder="Email"
									@input="clearErrorMessage"
							>
							<div class="form-control-position">
								<i class="icon-user"></i>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="password" class="sr-only">Password</label>
						<div class="position-relative has-icon-right">
							<input
									v-model="password"
									type="password"
									id="password"
									class="form-control input-shadow"
									placeholder="Lozinka"
									@input="clearErrorMessage"
							>
							<div class="form-control-position">
								<i class="icon-lock"></i>
							</div>
						</div>
					</div>

					<button type="submit"
							class="btn btn-light btn-block"
							:class="{'disabled': submitting}"
					>
						Login
					</button>

					<label class="error" v-if="errorMessage">
						<i class="fa fa-exclamation"></i>
						{{ errorMessage }}
					</label>

				</form>

			</div>
		</div>
	</div>

</template>

<script type="text/babel">

	import OverlayMixin from 'ionMixins/OverlayMixin.js';

	export default {
		mixins: [OverlayMixin],

		props: [

		],

		data: function() {
			return {
				email: null,
				password: null,
				submitting: false,
				errorMessage: null,
			}
		},

		mounted: function() {
			this.removeOverlay();
			sc.get('DataProvider').resetInitialData();
		},

		methods: {
			submit: function() {

				if (this.submitting) {
					return;
				}

				this.submitting = true;
				this.clearErrorMessage();

				let comp = this;
				let authData = {
					email: comp.email,
					password: comp.password
				};
				let attemptLogin = new Promise((resolve, reject) => { sc.get('Auth').login(authData, resolve, reject) });

				attemptLogin.then(function() {
					setTimeout(function() {
						comp.submitting = false;
					}, 500);

					comp.$router.push({ name: 'home' });

				}).catch(function(jqXHR) {
					comp.submitting = false;
					let errorCode = parseInt(jqXHR.status);
					let errorMessage = jqXHR.responseJSON;

					if (errorCode === 419) {
						location.reload();
					} else if (_.includes([401, 403], errorCode)) {
						comp.errorMessage = errorMessage;
					} else {
						comp.errorMessage = "An error has occurred. Please refresh the page and try again.";
					}

				});

			},

			clearErrorMessage: function() {
				this.errorMessage = null;
			}
		},

		computed: {

		}
	}
</script>