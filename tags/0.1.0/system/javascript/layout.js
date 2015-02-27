// gestion popin
var popin;

var Layout = {
	init : function () {
		Layout.setFooter();
		window.addEvent('resize', function() {
			Layout.setFooter();
		});
		if ($('btn_deco')){
			$('btn_deco').addEvent("mouseout", function () {
				$('btn_deco').src = '../../images/header/deco_on.png';
			});
			$('btn_deco').addEvent("mouseover", function () {
				$('btn_deco').src = '../../images/header/deco_off.png';
			});
		}

		Layout.initialiserCalendriers();

		new Tips($$('.tooltip'), {
			showDelay: 100
		});

		if ($('debug-zone')) {
			Console.descendre();
		}

		if ($('popin')) popin = new Popin($('popin'));
	},

	initialiserCalendriers: function (classe) {
		if (!classe) classe = '.calendrier';
		// Calendriers
		$$(classe).each( function(element) {
			var inputForCalendar = new Element('img', {id: 'btn_'+element.id});
			inputForCalendar.src = './images/lib/calendar/calendrier.gif';
			inputForCalendar.inject(element, 'after');
			inputForCalendar.style.cursor = "pointer";

			if(inputForCalendar) {
				inputForCalendar.addEvent('click', function() {
					displayCalendar(element, 'dd/mm/yyyy', inputForCalendar);
				});
			}

		});
	},

	setFooter : function () {
	}
};
window.addEvent('domready', Layout.init);

function sexyButtons(element) {
	element = (element) ? element : $$('body')[0];
	element.getElements('a.sexybutton').removeEvents('mouseover');
	element.getElements('a.sexybutton').removeEvents('mouseleave');
	element.getElements('a.sexybutton').addEvents({
		'mouseover': function() {
			this.addClass('active');
		},
		'mouseleave': function() {
			this.removeClass('active');
		}
	});
}

window.addEvent('domready', function () {
	popupConfirmJs();
});

function popupConfirmJs() {
	$$('a.confirmation').removeEvents();
	$$('a.confirmation').addEvent('click', function(event) {
		event.stop();
		Notification.confirmation(this.title, this.href);
		return false;
	});
}

var ie6 = (Browser.Engine.trident && Browser.Engine.version == 4);

var EditerMotDePasse = {
	setForm: function() {
		var contenu_popin = $$('#popin div.popin_middle>.popin_contenu')[0];
		new FormCheck('formulaire_popin', {
			submitByAjax: true,
			ajaxResponseDiv: contenu_popin,
			onAjaxSuccess :	function(){
				contenu_popin.innerHTML = contenu_popin.innerHTML.replace(/^ok/, '').trim();
				if ($('formulaire_popin')) EditerMotDePasse.setForm();
			}
		});
	}
};
var Fonctions = {

		/**
		 * Formate un chiffre avec nb_decimales chiffres apr�s la virgule et un separateur
		 * @param valeur : Valeur � formater
		 * @param nb_decimales : Nombre de d�cimales � apposer
		 * @param separateur : S�parateur de millier � utiliser
		 */
		formater: function (valeur, nb_decimales, separateur) {
			valeur = valeur.toString().replace(',', '.');
			if (isNaN(valeur)) valeur = 0;
			if (!nb_decimales) nb_decimales = 3;
			if (!separateur) separateur = ' ';

			// On va travailler avec la valeur absolue
			var valeur_absolue = Math.abs(valeur);
			// On r�cup�re le nombre d�cimal, format� en entier et pr�coup�
			var valeur_decimale = Math.round( Math.pow(10, nb_decimales) * (valeur_absolue - Math.floor( valeur_absolue )) );
			// On r�cup�re la valeur enti�re
			var valeur_entiere = Math.floor( valeur_absolue );

			if (( nb_decimales == 0 ) || ( valeur_decimale == Math.pow(10, nb_decimales)) ) {
				valeur_entiere = Math.floor( valeur_absolue );
				valeur_decimale = 0;
			}
			var valeur_formatee = valeur_entiere + "";

			// On d�termine ici la taille de la valeur enti�re, afin d'ins�rer les s�parateurs
			if (separateur) {
				var taille_valeur = valeur_formatee.length;
				for (var i = 1 ; i < 4 ; i++) {
					if (valeur_entiere >= Math.pow(10, (3 * i))) {
						valeur_formatee = valeur_formatee.substring(0, taille_valeur - (3 * i)) + separateur + valeur_formatee.substring(taille_valeur - (3 * i));
					}
				}
			}

			// On g�re ici les d�cimales manquantes au d�but
			if (nb_decimales > 0) {
				var zeros_manquants = "";
				for (var j = 0 ; j < (nb_decimales - valeur_decimale.toString().length) ; j++) {
					zeros_manquants += "0";
				}
				valeur_decimale = zeros_manquants + valeur_decimale.toString();
				valeur_formatee = valeur_formatee + "." +  valeur_decimale;
			}

			// Gestion du n�gatif
			if (parseFloat(valeur) < 0) {
				valeur_formatee = "-" + valeur_formatee;
			}
			valeur_formatee = valeur_formatee.replace('.', ',');

			return valeur_formatee;
		},

		/**
		 * D�-formate un nombre, et lui retirant les s�parateurs et les virgules
		 * @param valeur : Valeur � purifier
		 * @param separateur_decimal : S�parateur de d�cimal utilis�
		 * @param separateur_millier : S�parateur de millier utilis�
		 */
		purifier: function (valeur, separateur_decimal, separateur_millier) {
			if (!separateur_decimal) separateur_decimal = ',';
			if (!separateur_millier) separateur_millier = ' ';
			return parseFloat(valeur.toString().replace(separateur_millier, '').replace(separateur_millier, '').replace(separateur_decimal, '.'));
		}
};