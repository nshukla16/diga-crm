## Installation

Nginx: Bind folder vendor/afterlogic_webmail to uri /webmail

proceed to link:

/install

Incoming mail:
mail.rkesa.pt:993 ssl

Outgoing mail:
mail.rkesa.pt:25 non-ssl

!!! Important
For every account set in settings smtp server: mail.rkesa.pt:25 non-ssl

If you want to add second account:
window.App.Api.createMailAccount()

## For developers
/vendor/afterlogic_webmail/data/plugins/email-hooks

/vendor/afterlogic_webmail/data/plugins/erp-contacts

Adicionar ao converça interface button in:

/vendor/afterlogic_webmail/templates/views/Mail/LayoutSidePane/MessagePaneViewModel.html

Button translations in

/vendor/afterlogic_webmail/i18n/Portuguese-Portuguese.ini

Clear cache if you edit files (`data/cache`)