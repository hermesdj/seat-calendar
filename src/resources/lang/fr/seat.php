<?php

return [

    'plugin_name' => 'Calendrier',

    'settings' => 'Configuration',
    'operations' => 'Operations',

    'all_operations' => 'Toutes',
    'incoming_operations' => 'A Venir',
    'cancelled_operations' => 'Annulées',
    'ongoing_operations' => 'En Cours',
    'faded_operations' => 'Anciennes',

    'add_operation' => 'Créer une nouvelle opération',
    'known_duration' => 'Durée est connue',

    'close' => 'Fermer',
    'close_confirm_notice' => "Etes-vous certain de vouloir fermer cette opération ? Fermer une opération va mettre sa date de fin à maintenant, ce qui ne l'affichera plus dans la section En cours",
    'close_confirm_button_no' => 'Non, ne pas fermer cette opération',
    'close_confirm_button_yes' => 'Oui, je veux fermer cette opération',
    'confirm' => 'Confirmer',
    'delete' => 'Supprimer',
    'delete_confirm_notice' => 'Etes vous certain de vouloir supprimer cette opération ? Cette action est irréversible.',
    'delete_confirm_button_no' => 'Non, ne pas supprimer cette opération',
    'delete_confirm_button_yes' => 'Oui, je suis sûr de vouloir supprimer cette opération.',
    'update' => 'Modifier',
    'cancel' => 'Annuler',
    'cancelled' => 'Annulé',

    'cancel_confirm_notice' => 'Êtes-vous sûr(e) de vouloir annuler cette opération ? Une opération annuler sera visible dans la section "Anciennes". Vous pouvez réactiver une opération annulée.',
    'cancel_confirm_button_no' => 'Non, n\'annulez pas cette opération.',
    'cancel_confirm_button_yes' => 'Oui, je veux annuler cette opération',
    'other' => 'Autres',
    'activate' => 'Activer',
    'activate_confirm_notice' => 'Êtes-vous sûr(e) de vouloir réactiver cette opération ? Cette opération ne sera plus considéré comme "annulée".',
    'activate_confirm_button_no' => 'Non, ne réactiver pas cette opération.',
    'activate_confirm_button_yes' => 'Oui, je veux réactiver cette opéartion.',
    'details' => 'Détails',
    'time' => 'Heure',
    'yes' => 'Oui',
    'no' => 'Non',
    'actions' => 'Actions',
    'attending_yes' => 'Participe',
    'attending_no' => 'Ne participe pas',
    'attending_maybe' => 'Participe peut être',
    'create_confirm_button_yes' => 'Créer cette opération',
    'update_confirm_button_yes' => 'Confirmer modification',
    'subscribe_confirm_button_yes' => 'Envoyer son status',
    'subscription' => 'Enregistrement',
    'subscribe' => "S'enregistrer",
    'not_answered' => 'Non répondu !',
    'none' => 'Aucun',
    'status' => 'Status',
    'answered_at' => 'Répondu le',
    'unknown' => 'Inconnu',
    'informations' => 'Informations',
    'attendees' => 'Participants',
    'confirmed' => 'Confirmé',

    'month' => 'mois',
    'day' => 'jour|jours',
    'hour' => 'heure|heures',
    'minute' => 'minute|minutes',
    'second' => 'seconde|secondes',

    'placeholder_title' => "Nom de l'Opération",
    'placeholder_staging' => 'Lieu de Rencontre (système, station, citadelle...)',
    'placeholder_staging_sys' => 'Système de Rencontre',
    'placeholder_staging_info' => "Plus d'informations sur le lieu de rencontre",
    'placeholder_fc' => 'Nom du commandant de Flotte',
    'placeholder_description' => 'Informations additionnelles à propos de l\'opération. Ce champ accepte le BBCode.',
    'placeholder_comment' => 'Informations Additionnelles',

    'created_at' => 'Créé le',
    'updated_at' => 'Modifié le',
    'created_by' => 'Créé par',

    'title' => 'Titre',
    'type' => 'Type',
    'tags' => 'Tags',
    'description' => 'Description',
    'comment' => 'Commentaires',
    'starts_at' => 'Commence le',
    'eve_time' => 'heure EVE',
    'local_time' => 'heure locale',
    'starts_in' => 'Commence dans',
    'started' => 'Commencé',
    'started_at' => 'Commencé à',
    'ends_at' => 'Termine à',
    'ends_in' => 'Termine dans',
    'ended_at' => 'Terminé à',
    'duration' => 'Durée',
    'lasted' => 'Lasted',
    'importance' => 'Importance',
    'staging' => 'Rendez-Vous',
    'staging_sys' => 'Système Rendez-Vous',
    'staging_info' => 'Staging info',
    'fleet_commander' => 'Commandant de Flotte',
    'staging_system' => 'Staging System',
    'character' => 'Personnage',

    'notification_enable' => 'Notifier sur Slack',
    'integration_channel' => 'Salon d\'intégration',

    'notification_operation_posted_label' => 'Calendrier: Nouvelle Opération',
    'notification_operation_activated_label' => 'Calendrier: Opération activée',
    'notification_operation_cancelled_label' => 'Calendrier: Operation annulée',
    'notification_operation_ended_label' => 'Calendrier: Operation terminée',
    'notification_operation_pinged_label' => 'Calendrier: Opération ping',
    'notification_operation_updated_label' => 'Calendrier: Opération mise à jour',

    'notifications_to_send' => 'Notifications à envoyer',

    'help_notify_locale' => 'Sélectionnez la langue par défaut à utiliser pour les notifications.',
    'notify_locale' => 'Langue par défaut',

    'help_notify_operation_interval' => 'Décidez de combien de ping vous voulez envoyer avant chaque opération. Chaque valeur est un nombre de minutes précédent l\'opération d\'envoi du ping. Séparez les nombres par des virgules. Les valeurs par défaut de :default_interval envoie trois notifications : 15 minutes, 30 minutes et 60 minutes avant le début de l\'opération.',
    // 'Decide how many pings to send before each operation. Each value is the number of minutes prior to the operation to send the ping. Separate numbers with commas. Default value of :default_interval will send 3 notifications: 15 minutes, 30 minutes, and 60 minutes prior to the operation start time.'
    'ping_intervals' => 'Intervalles des ping',

    'slack_integration' => 'Intégration Slack',
    'discord_integration' => 'Intégration Discord',
    'enabled' => 'Activée',
    'default_channel' => 'Salon par défaut',
    'create_operation' => 'Créer une opération',
    'cancel_operation' => 'Annulée une opération',
    'end_operation' => 'Finir une opération',
    'update_operation' => 'Mettre à jour une opération',
    'activate_operation' => 'Réactiver une opération annulée',
    'webhook' => 'Webhook',
    'emoji_full' => 'Full Emoji',
    'emoji_half' => 'Half Emoji',
    'emoji_empty' => 'Empty Emoji',
    'help_emoji' => 'Définissez chaque emoji a faire apparaitre pour montrer l\'importance d\'une opération lorsque cela apparait sur Slack.',
    // Setup which emoji to use to display the "importance" of an operation when relaying to Slack.
    'save' => 'Sauvegarder',

    'discord_client_id' => 'Discord Client Id',
    'discord_client_secret' => 'Discord Client Secret',
    'discord_bot_token' => 'Discord Bot Token',

    'warning_no_character' => 'Vous ne pouvez pas vous abonnez à une opération sans personnage enregistrer dans SeAT. Ajoutez une clé API et réessayer.',

    'in' => 'dans',
    'to' => 'à',

    'new' => 'Nouveau',
    'edit' => 'Éditer',

    'name' => 'Nom',
    'background' => 'Arrière-plan',
    'text_color' => 'Couleur du texte',
    'preview' => 'Aperçu',
    'order' => 'Priorité',

    'name_tag_placeholder' => 'Nom du tag... 25 caractères maximum.',
    'background_placeholder' => 'Couleur de l\'arrière-plan... #000000',
    'text_color_placeholder' => 'Couleur du texte... #FFFFFF',
    'order_placeholder' => 'Pour trier (numerique). Valeur faible afficher en premier.',
    'select_role_filter_placeholder' => 'Sélectionner le rôle à resteindre',

    'delete_tag_confirm_button_no' => 'Non, ne supprimez pas ce tag',
    'delete_tag_confirm_button_yes' => 'Oui, je suis sûre de supprimer ce tag',

    'direct_link' => 'Lien direct',

    'add_to_calendar' => 'Ajouter au calendrier',
    'google_calendar' => 'Calendrier Google',

    'paps' => 'Paps',

    'analytic' => 'Axe d\'analyse',
    'quantifier' => 'Quantifier',
    'strategic' => 'Stratégie',
    'pvp' => 'PvP',
    'mining' => 'Minage',
    'untracked' => 'Non suivi',
    'list' => 'Liste',

    // New
    'delete_tag_description' => 'Cette action va définitivement supprimer ce tag, en êtes vous sûr ?',

    'doctrines' => 'Doctrine',
    'track_fleet' => 'Suivre la flotte (ESI)',
];
