<?php
require_once('ModuleDesWidgetInterface.php');

class ModuleDesWidget extends WP_Widget implements ModuleDesWidgetInterface
{
    private static $resultFinal = '';

    // cette méthode construit le widget. Elle devra faire appel au constructeur de la classe parent et accrochera au crochet wp_head le css défini dans la fonction css 
    // afin de pouvoir lister les 500 derniers jets j'ai décidé de créer un shortcode défini par la fonction liste_jets et accroché avec le shortcode liste_jets grâce à la fonction add_shortcode
    public function __construct()
    {
        parent::__construct('ModuleDes', 'Module de lancement de Dès', ['description', 'Module de lancement de Dés']);

        if (is_active_widget(false, false, $this->id_base)){
            add_action('wp_head', [$this, 'css']);
        }

        add_shortcode('liste_jets', [$this, 'liste_jets']);
    }

    // la fonction form permet de créer le formulaire de paramétrage du widget
    // ici j'ai 2 paramètres : le nom du widget et la liste des dés que l'on peut lancer
    public function form($instance)
    {
        if (isset($instance['title'])){
            $title = $instance['title'];
        } else {
            $title = '';
        }

        if (isset($instance['listeDes'])) {
            $listeDes = $instance['listeDes'];
        } else {
            $listeDes = '2,4,6,8,10,20,100';
        }
        ?>

        <p>
            <label for="<?= $this->get_field_name('title'); ?>"><?php _e('title:'); ?></label>
            <input class="widefat" id="<?= $this->get_field_id('title'); ?>" name="<?= $this->get_field_name('title'); ?>" type="text" value="<?= $title; ?>" />
        </p>
        <p>
            <label for="<?= $this->get_field_name('listeDes'); ?>"><?php _e('liste Des:'); ?></label>
            <input class="widefat" id="<?= $this->get_field_id('listeDes'); ?>" name="<?= $this->get_field_name('listeDes'); ?>" type="text" value="<?= $listeDes; ?>" />
        </p>

    <?php
    }

	// Fonction définissant un peu de css
    public function css()
    { ?>
        <style type="text/css">
            /* .row-Module{
                width: 100%;
                display: flex;
                flex-wrap: wrap;
            } */
            .champ{
                padding: 10px;
            }
            .center{
                text-align: center;
            }
            input[type=submit]{
                margin: 10px;
                padding: 10px;
                font-size: 1em;
            }
            .resultFinal{
                color: red;
            }
            </style>
    <?php 
    } 

	// Cette fonction contient le corps du module, c'est en appelant cette méthode qu'on affiche le widget
    public function widget($args, $instance)
    {
        $listeDes = explode(',', $instance['listeDes']);
        sort($listeDes);
        echo $args['before_widget'];
        echo $args['before_title'];
        echo apply_filters('widget_title', $instance['title']);

        if (!empty(self::$resultFinal)) { ?>
            <div class='resultFinal'>
                <?= apply_filters('widget_title', self::$resultFinal); ?>
            </div>
        <?php self::$resultFinal = "";
        }

        echo $args['after_title'];
        ?>
            <form action="" method="post">
                <?php 
                foreach ($listeDes as $de) {
                ?>
                <div class="row-Module">
                    <div class="champ">
                        <input type="number" name="nbd[<?= $de; ?>]" id="nbd<?= $de; ?>" min="0" max="50" value="0">
                        <label for="nbd<?= $de; ?>"><?= $de; ?> faces</label>
                    </div>
                </div>
                <?php } ?>
                <div class="row-Module">
                    <div class="champ">
                        <input type="number" name="constante" id="constante" min="0" max="50" value="0">
                        <label for="constante">Ajout constante</label>
                    </div>
                </div>
                <div class="center">
                    <input type="submit" name="sub" value="Lancer les dés">
                </div>
            </form>
        <?php
        echo $args['after_widget'];
    }

    // Cette methode me permet de créer la table qui stockera tous les jets réalisés
    public static function install()
    {
        global $wpdb;
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jet (id INT AUTO_INCREMENT PRIMARY KEY,
        date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP, jet TEXT NOT NULL, userid INT NOT NULL);");
    }

    // Cette méthode supprime la table de stockage des jets
    public static function uninstall()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}jet;");
    }

	// Cette fonction me permet de renvoyer un tableau contenant $nb nombres compris entre 0 et $max
    public static function alea($max, $nb)
    {
        $arrayToReturn = [];

        for ($i=0; $i<$nb; $i++) {
            $arrayToReturn[] = rand(1, $max);
        }

        return $arrayToReturn;
    }

	// C'est dans cette fonction que je détermine le comportement de mon shortcode
    public static function liste_jets($atts, $content)
    {
        $atts = shortcode_atts(['numberjets'=>500], $atts);
        global $wpdb;
        $html = [];
        $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}jet ORDER BY id DESC LIMIT ".$atts['numberjets'], OBJECT);
        $html[] = $content;

        foreach ($results as $jet) {
            $user = get_userdata($jet->userid);
            $html[] = "- <b>".$user->data->display_name." (".$jet->date.")</b> : ".$jet->jet."<br>";
        }

        echo implode("", $html);
    }

	// C'est dans cette methode que je vais stocker les éléments des jets
    public static function traitement()
    {
        global $wpdb;

        if (isset($_POST['sub'])) {
            $jet = [];
            $somme = $_POST['constante'];

            foreach($_POST['nbd'] as $key => $value) {
                if ($value > 0){
                    $jetTmp = self::alea($key, $value);
                    $jet[] = $value . 'd'.$key." [ ".implode(", ", $jetTmp). " ]";
                    $somme += array_sum($jetTmp);
                }
            }

            self::$resultFinal = implode(" + ", $jet);

            if (!empty($_POST['constante'])) {
                self::$resultFinal .= " + ".$_POST['constante'];
            }

            self::$resultFinal .= " = ". $somme;
            $wpdb->insert("{$wpdb->prefix}jet", ['jet' => self::$resultFinal, 'date' => date("Y-m-d H:i:s"), 'userid' => get_current_user_id()]);

            wp_safe_redirect(wp_get_referer());
        }
    }
}