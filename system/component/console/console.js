var Console = {
	taille_normale: true,
	
	detacher: function () {
		Console.cacher();
		open('/console/afficher_console', '_blank', 'height=570px,width=460px');
	},

	eteindre: function () {
		new Request({
			url: '/console/activer_console?statut_console=eteinte',
			onSuccess: function (retour) {
				$('debug-zone').hide();
				$('bouton_cacher').hide();
				$('bouton_ouvrir').show();
				$('bloc_boutons').hide();
				$('bouton_eteindre').hide();
				$('bouton_allumer').show();
			}
		}).send();
	},
	
	allumer: function () {
		new Request({
			url: '/console/activer_console?statut_console=veille',
			onSuccess: function (retour) {
				$('bloc_boutons').show();
				$('bouton_allumer').hide();
				$('bouton_eteindre').show();
			}
		}).send();
	},
	
	cacher: function () {
		new Request({
			url: '/console/activer_console?statut_console=veille',
			onSuccess: function (retour) {
				$('debug-zone').hide();
				$('bouton_cacher').hide();
				$('bouton_ouvrir').show();
			}
		}).send();
	},

	ouvrir: function () {
		new Request({
			url: '/console/activer_console?statut_console=active',
			onSuccess: function (retour) {
				$('debug-zone').show();
				$('bouton_cacher').show();
				$('bouton_ouvrir').hide();
			}
		}).send();
	},

	agrandir: function () {
		if (Console.taille_normale) {
			Console.taille_normale = false;
			$('debug-zone').setStyle('left', '1em');
			$('debug-zone').setStyle('bottom', '1em');
			$('debug-zone').setStyle('width', 'auto');
			$('debug-zone').setStyle('height', 'auto');
		} else {
			Console.taille_normale = true;
			$('debug-zone').setStyle('left', '');
			$('debug-zone').setStyle('bottom', '');
			$('debug-zone').setStyle('width', '40em');
			$('debug-zone').setStyle('height', '50em');
		}
	},

	rafraichir: function () {
		new Request({
			url: '/console/rafraichir_console',
			onSuccess: function (retour) {
				$('debug-contenu').innerHTML = retour;
			}
		}).send();
	},

	vider: function () {
		new Request({
			url: '/console/vider_console',
			onSuccess: function (retour) {
				$('debug-contenu').innerHTML = '';
			}
		}).send();
	},

	descendre: function () {
		new Fx.Scroll('debug-zone').toBottom();
	},
	
	activer: function (valeur, option) {
		new Request({
			url: '/console/activer_option_console?option=' + option + '&valeur=' + valeur,
			onSuccess: function (retour) {
				if (valeur) {
					$$('.log_' + option).show();
					$('bouton_activer_' + option).hide();
					$('bouton_desactiver_' + option).show();
				} else {
					$$('.log_' + option).hide();
					$('bouton_activer_' + option).show();
					$('bouton_desactiver_' + option).hide();
				}
			}
		}).send();
	}
};