<?php
interface ModuleDesWidgetInterface
{
    // cette méthode construit le widget. Elle devra faire appel au constructeur de la classe parent et accrochera au crochet wp_head le css défini dans la fonction css 
    // afin de pouvoir lister les 500 derniers jets j'ai décidé de créer un shortcode défini par la fonction liste_jets et accroché avec le shortcode liste_jets grâce à la fonction add_shortcode
    public function __construct();

    // la fonction form permet de créer le formulaire de paramétrage du widget
    // ici j'ai 2 paramètres : le nom du widget et la liste des dés que l'on peut lancer
	public function form($instance);

	// Fonction définissant un peu de css
	public function css(); 

	// Cette fonction contient le corps du module, c'est en appelant cette méthode qu'on affiche le widget
    public function widget($args, $instance);

    // Cette methode me permet de créer la table qui stockera tous les jets réalisés
    public static function install();

    // Cette méthode supprime la table de stockage des jets
	public static function uninstall();

	// Cette fonction me permet de renvoyer un tableau contenant $nb nombres compris entre 0 et $max
	public static function alea($max, $nb);

	// C'est dans cette fonction que je détermine le comportement de mon shortcode
	public static function liste_jets($atts, $content);

	// C'est dans cette methode que je vais stocker les éléments des jets
    public static function traitement();
}