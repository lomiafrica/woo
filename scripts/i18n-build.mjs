import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const domain = 'woo-lomi';
const locale = 'fr_FR';
const langDir = path.join(root, 'languages');

const sourceRoots = [
	'woo-lomi.php',
	'includes',
	'resources',
];

const pluginHeaders = [
	'lomi. for WooCommerce',
	'WooCommerce payment gateway for lomi.',
	'lomi.',
	'https://lomi.africa',
];

const scriptHandles = [
	'wc-lomi-blocks',
];

const translations = {
	'Accept payments with lomi. Secure hosted checkout. <a href="%1$s" target="_blank">Create an account</a> and <a href="%2$s" target="_blank">get your API keys</a>.': 'Acceptez les paiements avec lomi. via une page de paiement securisee. <a href="%1$s" target="_blank">Creez un compte</a> et <a href="%2$s" target="_blank">recuperez vos cles API</a>.',
	'Account': 'Compte',
	'Additional lomi. Gateways': 'Passerelles lomi. supplementaires',
	'Additional lomi. payment method using shared API keys from the main lomi. gateway. <a href="%1$s" target="_blank">lomi.</a> · <a href="%2$s" target="_blank">Dashboard</a>.': 'Methode de paiement lomi. supplementaire utilisant les cles API partagees de la passerelle lomi. principale. <a href="%1$s" target="_blank">lomi.</a> · <a href="%2$s" target="_blank">Tableau de bord</a>.',
	'1 gateway': '1 passerelle',
	'2 gateways': '2 passerelles',
	'3 gateways': '3 passerelles',
	'4 gateways': '4 passerelles',
	'5 gateways': '5 passerelles',
	'Allowed Card Brands': 'Marques de carte autorisees',
	'A flat fee to charge the subaccount for this transaction, in the order currency. This overrides the split percentage set when the subaccount was created. Use this for flat-rate splits, for example 100 for a 100-unit flat fee.': 'Frais fixes a facturer au sous-compte pour cette transaction, dans la devise de la commande. Cela remplace le pourcentage de repartition defini lors de la creation du sous-compte. Utilisez cette option pour les repartitions forfaitaires, par exemple 100 pour des frais fixes de 100 unites.',
	'Autocomplete Order': 'Finaliser automatiquement la commande',
	'Autocomplete Order After Payment': 'Finaliser automatiquement la commande apres paiement',
	'Automatic refunds from WooCommerce are not available for lomi.. Process refunds in your lomi. dashboard.': 'Les remboursements automatiques depuis WooCommerce ne sont pas disponibles pour lomi.. Effectuez les remboursements dans votre tableau de bord lomi.',
	'Automatic subscription renewals are not charged in WooCommerce with lomi. hosted checkout. Collect payment via a new checkout session or from the lomi. dashboard.': 'Les renouvellements automatiques d\'abonnement ne sont pas debites dans WooCommerce avec la page de paiement hebergee lomi. Encaissez le paiement via une nouvelle session de paiement ou depuis le tableau de bord lomi.',
	'Cancel order & restore cart': 'Annuler la commande et restaurer le panier',
	'Cards': 'Cartes',
	'Could not verify payment with lomi..': 'Impossible de verifier le paiement avec lomi..',
	'Configure a webhook in your <a href="%1$s" target="_blank" rel="noopener noreferrer">lomi. dashboard</a> with URL: <code style="color:red">%2$s</code> and the same signing secret as below.': 'Configurez un webhook dans votre <a href="%1$s" target="_blank" rel="noopener noreferrer">tableau de bord lomi.</a> avec l\'URL: <code style="color:red">%2$s</code> et le meme secret de signature que ci-dessous.',
	'Create additional lomi. payment methods that use the same API keys as the main gateway, with custom checkout labels and icons.': 'Creez des methodes de paiement lomi. supplementaires qui utilisent les memes cles API que la passerelle principale, avec des libelles et des icones de paiement personnalises.',
	'Custom Metadata': 'Metadonnees personnalisees',
	'Customer Email': 'E-mail client',
	'Customer Name': 'Nom du client',
	'Customer Phone': 'Telephone du client',
	'Currency mismatch while verifying payment with lomi.. Please contact the store.': 'La devise ne correspond pas lors de la verification du paiement avec lomi.. Veuillez contacter la boutique.',
	'lomi.': 'lomi.',
	'Description': 'Description',
	'Disable lomi. test mode': 'Desactiver le mode test lomi.',
	'Enable Custom Metadata': 'Activer les metadonnees personnalisees',
	'Enable lomi.': 'Activer lomi.',
	'Enable lomi. - %s': 'Activer lomi. - %s',
	'Enable lomi. on the checkout page.': 'Activer lomi. sur la page de paiement.',
	'Enable Payment via Saved Cards': 'Activer le paiement par cartes enregistrees',
	'Enable Split Payment': 'Activer le paiement fractionne',
	'Enable Test Mode': 'Activer le mode test',
	'Enable this gateway as a payment option on the checkout page.': 'Activer cette passerelle comme option de paiement sur la page de commande.',
	'Enable/Disable': 'Activer/Desactiver',
	'Enter the subaccount code here.': 'Saisissez le code du sous-compte ici.',
	'Five': 'Cinq',
	'Four': 'Quatre',
	'If checked, the customer email address will be sent to lomi.': 'Si cette option est cochee, l\'adresse e-mail du client sera envoyee a lomi.',
	'If checked, the customer full name will be sent to lomi.': 'Si cette option est cochee, le nom complet du client sera envoye a lomi.',
	'If checked, the customer phone will be sent to lomi.': 'Si cette option est cochee, le telephone du client sera envoye a lomi.',
	'If checked, the Order ID will be sent to lomi.': 'Si cette option est cochee, l\'ID de commande sera envoye a lomi.',
	'If checked, the order billing address will be sent to lomi.': 'Si cette option est cochee, l\'adresse de facturation sera envoyee a lomi.',
	'If checked, the order shipping address will be sent to lomi.': 'Si cette option est cochee, l\'adresse de livraison sera envoyee a lomi.',
	'If checked, the product(s) purchased will be sent to lomi.': 'Si cette option est cochee, les produits achetes seront envoyes a lomi.',
	'If enabled, the order will be marked as complete after successful payment': 'Si cette option est activee, la commande sera marquee comme terminee apres un paiement reussi.',
	'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on lomi. servers, not on your store.<br>Note that you need to have a valid SSL certificate installed.': 'Si cette option est activee, les utilisateurs pourront payer avec une carte enregistree lors du paiement. Les details de carte sont stockes sur les serveurs de lomi., pas sur votre boutique.<br>Notez qu\'un certificat SSL valide doit etre installe.',
	'If enabled, you will be able to send more information about the order to lomi..': 'Si cette option est activee, vous pourrez envoyer davantage d\'informations sur la commande a lomi..',
	'Important: configure your webhook in the <a href="%s" target="_blank" rel="noopener noreferrer">lomi. dashboard</a> using the URL below.': 'Important: configurez votre webhook dans le <a href="%s" target="_blank" rel="noopener noreferrer">tableau de bord lomi.</a> avec l\'URL ci-dessous.',
	'Legacy option. All payments use lomi. hosted checkout.': 'Option historique. Tous les paiements utilisent la page de paiement hebergee lomi.',
	'Legacy setting. Customers are always sent to lomi. hosted checkout.': 'Reglage historique. Les clients sont toujours envoyes vers la page de paiement hebergee lomi.',
	'Live Public Key': 'Cle publique de production',
	'Live Secret Key': 'Cle secrete de production',
	'Live webhook secret': 'Secret webhook de production',
	'lomi. for WooCommerce': 'lomi. pour WooCommerce',
	'lomi.': 'lomi.',
	'lomi. ': 'lomi. ',
	'lomi. - %s': 'lomi. - %s',
	'lomi.: amount mismatch (order expects %1$s; session has %2$s).': 'lomi.: montant different (la commande attend %1$s; la session contient %2$s).',
	'lomi.: checkout session response missing checkout_session_id or checkout_url.': 'lomi.: la reponse de session de paiement ne contient pas checkout_session_id ou checkout_url.',
	'lomi.: could not fetch checkout session from API after customer return.': 'lomi.: impossible de recuperer la session de paiement depuis l\'API apres le retour du client.',
	'lomi.: currency mismatch (order %1$s vs session %2$s).': 'lomi.: devise differente (commande %1$s contre session %2$s).',
	'lomi.: failed to create checkout session (%s)': 'lomi.: echec de creation de la session de paiement (%s)',
	'lomi.: PAYMENT_SUCCEEDED webhook received but session could not be fetched from API.': 'lomi.: webhook PAYMENT_SUCCEEDED recu, mais la session n\'a pas pu etre recuperee depuis l\'API.',
	'lomi.: return URL missing checkout session id in order meta.': 'lomi.: l\'URL de retour ne contient pas l\'ID de session de paiement dans les metadonnees de commande.',
	'lomi.: session not paid yet (status: %1$s, session: %2$s).': 'lomi.: session pas encore payee (statut: %1$s, session: %2$s).',
	'lomi. API request failed before receiving a response: %s': 'La requete API lomi. a echoue avant de recevoir une reponse: %s',
	'lomi. API request failed (HTTP %1$d): %2$s': 'La requete API lomi. a echoue (HTTP %1$d): %2$s',
	'lomi. API request failed.': 'La requete API lomi. a echoue.',
	'lomi. checkout session expired before payment; review or cancel the order.': 'La session de paiement lomi. a expire avant le paiement; verifiez ou annulez la commande.',
	'lomi. payment gateway disabled': 'Passerelle de paiement lomi. desactivee',
	'lomi. payment methods': 'Methodes de paiement lomi.',
	'lomi. payment successful (checkout session: %s)': 'Paiement lomi. reussi (session de paiement: %s)',
	'lomi. requires WooCommerce to be installed and active. Click %s to install WooCommerce.': 'lomi. necessite que WooCommerce soit installe et actif. Cliquez sur %s pour installer WooCommerce.',
	'lomi. supports XOF, USD, and EUR only. Set your store currency <a href="%s">here</a>.': 'lomi. prend uniquement en charge XOF, USD et EUR. Definissez la devise de votre boutique <a href="%s">ici</a>.',
	'lomi. test mode enabled': 'Mode test lomi. active',
	'lomi. test mode is currently enabled. Remember to disable it when you want to start accepting live payments on your site.': 'Le mode test lomi. est actuellement active. Pensez a le desactiver lorsque vous voulez accepter des paiements reels sur votre site.',
	'lomi. Transaction Failed (%s)': 'Transaction lomi. echouee (%s)',
	'lomi. WooCommerce settings': 'Reglages WooCommerce lomi.',
	'lomi. Charges Bearer': 'Responsable des frais lomi.',
	'lomi. Payment Gateway Disabled: %s': 'Passerelle de paiement lomi. desactivee: %s',
	'Pay securely with lomi.': 'Payez en toute securite avec lomi.',
	'Pay with lomi.': 'Payez avec lomi.',
	'Secured by lomi.': 'Securise par lomi.',
	'Mastercard': 'Mastercard',
	'Missing lomi. session. Please try again or contact the store.': 'Session lomi. manquante. Veuillez reessayer ou contacter la boutique.',
	'No response body.': 'Aucun corps de reponse.',
	'One': 'Une',
	'Optional. Not required for hosted checkout.': 'Facultatif. Non requis pour la page de paiement hebergee.',
	'Optional. Not required for hosted checkout; reserved for future use.': 'Facultatif. Non requis pour la page de paiement hebergee; reserve a une utilisation future.',
	'Order %s': 'Commande %s',
	'Order Billing Address': 'Adresse de facturation de la commande',
	'Order ID': 'ID de commande',
	'Order not found.': 'Commande introuvable.',
	'Order Shipping Address': 'Adresse de livraison de la commande',
	'Pay with Bank': 'Payer par banque',
	'Pay with lomi.': 'Payer avec lomi.',
	'Payment Channels': 'Canaux de paiement',
	'Payment Icons': 'Icones de paiement',
	'Payment Option': 'Option de paiement',
	'Please enter your lomi. secret API key %shere%s to use this gateway.': 'Veuillez saisir votre cle API secrete lomi. %sici%s pour utiliser cette passerelle.',
	'Popup': 'Popup',
	'Product(s) Purchased': 'Produit(s) achete(s)',
	'Redirect': 'Redirection',
	'Remove Cancel Order & Restore Cart Button': 'Retirer le bouton Annuler la commande et restaurer le panier',
	'Remove the cancel order & restore cart button on the pay for order page': 'Retirer le bouton Annuler la commande et restaurer le panier sur la page de paiement de commande',
	'Return to payments': 'Retour aux paiements',
	'Saved card payments are not available with lomi.': 'Les paiements par carte enregistree ne sont pas disponibles avec lomi.',
	'Saved cards are not supported with lomi. Use the hosted checkout.': 'Les cartes enregistrees ne sont pas prises en charge avec lomi. Utilisez la page de paiement hebergee.',
	'Saved Cards': 'Cartes enregistrees',
	'Secured by lomi. Blue': 'Securise par lomi. bleu',
	'Secured by lomi. White': 'Securise par lomi. blanc',
	'Secret for verifying webhook signatures (live).': 'Secret pour verifier les signatures webhook (production).',
	'Secret for verifying webhook signatures (test). Must match the secret configured on your lomi. webhook endpoint.': 'Secret pour verifier les signatures webhook (test). Il doit correspondre au secret configure sur votre endpoint webhook lomi.',
	'Settings': 'Reglages',
	'Select card brands': 'Selectionner les marques de carte',
	'Select One': 'Selectionner',
	'Select payment channels': 'Selectionner les canaux de paiement',
	'Select payment icons': 'Selectionner les icones de paiement',
	'Send Customer Email': 'Envoyer l\'e-mail client',
	'Send Customer Name': 'Envoyer le nom du client',
	'Send Customer Phone': 'Envoyer le telephone du client',
	'Send Order Billing Address': 'Envoyer l\'adresse de facturation',
	'Send Order ID': 'Envoyer l\'ID de commande',
	'Send Order Shipping Address': 'Envoyer l\'adresse de livraison',
	'Send Product(s) Purchased': 'Envoyer les produits achetes',
	'Split Payment': 'Paiement fractionne',
	'Split Payment Transaction Charge': 'Frais de transaction du paiement fractionne',
	'Subaccount': 'Sous-compte',
	'Subaccount Code': 'Code du sous-compte',
	'Test mode': 'Mode test',
	'Test mode uses the sandbox API. Disable for live payments.': 'Le mode test utilise l\'API sandbox. Desactivez-le pour les paiements reels.',
	'Test Public Key': 'Cle publique de test',
	'Test Secret Key': 'Cle secrete de test',
	'Test webhook secret': 'Secret webhook de test',
	'Thank you for your order. Pay securely with lomi. using the button below.': 'Merci pour votre commande. Payez en toute securite avec lomi. en utilisant le bouton ci-dessous.',
	'The card brands allowed for this gateway. This filter only works with the card payment channel.': 'Les marques de carte autorisees pour cette passerelle. Ce filtre fonctionne uniquement avec le canal de paiement par carte.',
	'The payment channels enabled for this gateway': 'Les canaux de paiement actives pour cette passerelle',
	'The payment icons to be displayed on the checkout page.': 'Les icones de paiement a afficher sur la page de commande.',
	'This controls the payment method description which the user sees during checkout.': 'Controle la description de la methode de paiement affichee au client lors du paiement.',
	'This controls the payment method title which the user sees during checkout.': 'Controle le titre de la methode de paiement affiche au client lors du paiement.',
	'This subscription has a free trial, reason for the 0 amount': 'Cet abonnement dispose d\'un essai gratuit, ce qui explique le montant de 0',
	'Three': 'Trois',
	'Title': 'Titre',
	'To configure your lomi. API keys and enable/disable test mode, do that <a href="%s">here</a>': 'Pour configurer vos cles API lomi. et activer/desactiver le mode test, faites-le <a href="%s">ici</a>',
	'Two': 'Deux',
	'Unable to process payment try again': 'Impossible de traiter le paiement. Veuillez reessayer',
	'Unexpected response from lomi. when creating checkout session.': 'Reponse inattendue de lomi. lors de la creation de la session de paiement.',
	'Unity Bank': 'Unity Bank',
	'unknown': 'inconnu',
	'Verve': 'Verve',
	'View lomi. WooCommerce settings': 'Voir les reglages WooCommerce lomi.',
	'Visa': 'Visa',
	'We could not confirm the payment amount with lomi.. Please contact the store.': 'Nous n\'avons pas pu confirmer le montant du paiement avec lomi.. Veuillez contacter la boutique.',
	'Who bears lomi. charges?': 'Qui prend en charge les frais lomi. ?',
	'WooCommerce payment gateway for lomi.': 'Passerelle de paiement WooCommerce pour lomi.',
	'You can only add a new card when placing an order.': 'Vous ne pouvez ajouter une nouvelle carte que lors du passage d\'une commande.',
	'Your lomi. payment was not completed. You can try again from the order payment page.': 'Votre paiement lomi. n\'a pas ete finalise. Vous pouvez reessayer depuis la page de paiement de la commande.',
	'Your lomi. secret API key (live).': 'Votre cle API secrete lomi. (production).',
	'Your lomi. secret API key (test).': 'Votre cle API secrete lomi. (test).',
	'https://lomi.africa': 'https://lomi.africa',
};

function walk(entry) {
	const full = path.join(root, entry);
	if (!fs.existsSync(full)) {
		return [];
	}

	const stat = fs.statSync(full);
	if (stat.isFile()) {
		return /\.(php|js)$/.test(entry) && !entry.endsWith('.min.js') && !entry.includes('assets/js/blocks/')
			? [entry]
			: [];
	}

	return fs.readdirSync(full, { withFileTypes: true }).flatMap((dirent) => {
		const child = path.join(entry, dirent.name).replace(/\\/g, '/');
		return dirent.isDirectory() ? walk(child) : walk(child);
	});
}

function unescapeString(value) {
	return value
		.replace(/\\'/g, "'")
		.replace(/\\"/g, '"')
		.replace(/\\\\/g, '\\')
		.replace(/\\n/g, '\n')
		.replace(/\\r/g, '\r')
		.replace(/\\t/g, '\t');
}

function poEscape(value) {
	return value
		.replace(/\\/g, '\\\\')
		.replace(/"/g, '\\"')
		.replace(/\t/g, '\\t')
		.replace(/\r/g, '\\r')
		.replace(/\n/g, '\\n');
}

function poString(kind, value) {
	if (!value.includes('\n') && value.length <= 78) {
		return `${kind} "${poEscape(value)}"\n`;
	}

	return `${kind} ""\n${value.split('\n').map((line, index, lines) => {
		const suffix = index < lines.length - 1 ? '\\n' : '';
		return `"${poEscape(line)}${suffix}"\n`;
	}).join('')}`;
}

function extractMessages() {
	const files = sourceRoots.flatMap(walk);
	const callNames = [
		'__',
		'_e',
		'esc_html__',
		'esc_html_e',
		'esc_attr__',
		'esc_attr_e',
	];
	const callPattern = callNames.map((name) => name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');
	const regex = new RegExp(`(?:${callPattern})\\s*\\(\\s*(['"])((?:\\\\.|(?!\\1)[\\s\\S])*?)\\1\\s*,\\s*['"]${domain}['"]`, 'g');
	const messages = new Map();

	for (const file of files) {
		const source = fs.readFileSync(path.join(root, file), 'utf8');
		let match;
		while ((match = regex.exec(source))) {
			const msgid = unescapeString(match[2]);
			if (!messages.has(msgid)) {
				messages.set(msgid, { refs: new Set() });
			}
			const line = source.slice(0, match.index).split(/\r\n|\r|\n/).length;
			messages.get(msgid).refs.add(`${file.replace(/\\/g, '/')}:${line}`);
		}
	}

	for (const header of pluginHeaders) {
		if (!messages.has(header)) {
			messages.set(header, { refs: new Set(['woo-lomi.php']) });
		}
	}

	return [...messages.entries()].sort(([a], [b]) => a.localeCompare(b));
}

function header(projectId, language = '') {
	return [
		'Project-Id-Version: lomi. for WooCommerce 1.001.1\\n',
		`Report-Msgid-Bugs-To: https://github.com/alphajoop/woocommerce/issues\\n`,
		`POT-Creation-Date: ${new Date().toISOString()}\\n`,
		`PO-Revision-Date: ${new Date().toISOString()}\\n`,
		'Last-Translator: lomi.\\n',
		'Language-Team: lomi.\\n',
		language ? `Language: ${language}\\n` : '',
		'MIME-Version: 1.0\\n',
		'Content-Type: text/plain; charset=UTF-8\\n',
		'Content-Transfer-Encoding: 8bit\\n',
		language === locale ? 'Plural-Forms: nplurals=2; plural=(n > 1);\\n' : 'Plural-Forms: nplurals=2; plural=(n != 1);\\n',
		'X-Domain: woo-lomi\\n',
	].join('');
}

function buildPot(messages) {
	let output = '# Copyright (C) 2026 lomi.\n';
	output += '# This file is distributed under the GPL-2.0+.\n';
	output += 'msgid ""\n';
	output += poString('msgstr', header('lomi. for WooCommerce'));
	output += '\n';

	for (const [msgid, meta] of messages) {
		output += [...meta.refs].map((ref) => `#: ${ref}\n`).join('');
		output += poString('msgid', msgid);
		output += 'msgstr ""\n\n';
	}

	return output;
}

function buildPo(messages) {
	let output = '# French translations for lomi. for WooCommerce.\n';
	output += '# This file is distributed under the GPL-2.0+.\n';
	output += 'msgid ""\n';
	output += poString('msgstr', header('lomi. for WooCommerce', locale));
	output += '\n';

	for (const [msgid, meta] of messages) {
		const msgstr = translations[msgid] ?? msgid;
		output += [...meta.refs].map((ref) => `#: ${ref}\n`).join('');
		output += poString('msgid', msgid);
		output += poString('msgstr', msgstr);
		output += '\n';
	}

	return output;
}

function buildMo(entries) {
	const sorted = [...entries.entries()].sort(([a], [b]) => a.localeCompare(b));
	const originals = sorted.map(([msgid]) => Buffer.from(`${msgid}\0`, 'utf8'));
	const translated = sorted.map(([, msgstr]) => Buffer.from(`${msgstr}\0`, 'utf8'));
	const count = sorted.length;
	const headerSize = 28;
	const originalTableOffset = headerSize;
	const translationTableOffset = originalTableOffset + count * 8;
	let stringOffset = translationTableOffset + count * 8;

	const originalTable = [];
	for (const buffer of originals) {
		originalTable.push({ length: buffer.length - 1, offset: stringOffset });
		stringOffset += buffer.length;
	}

	const translationTable = [];
	for (const buffer of translated) {
		translationTable.push({ length: buffer.length - 1, offset: stringOffset });
		stringOffset += buffer.length;
	}

	const output = Buffer.alloc(stringOffset);
	output.writeUInt32LE(0x950412de, 0);
	output.writeUInt32LE(0, 4);
	output.writeUInt32LE(count, 8);
	output.writeUInt32LE(originalTableOffset, 12);
	output.writeUInt32LE(translationTableOffset, 16);
	output.writeUInt32LE(0, 20);
	output.writeUInt32LE(0, 24);

	let tableOffset = originalTableOffset;
	for (const entry of originalTable) {
		output.writeUInt32LE(entry.length, tableOffset);
		output.writeUInt32LE(entry.offset, tableOffset + 4);
		tableOffset += 8;
	}

	tableOffset = translationTableOffset;
	for (const entry of translationTable) {
		output.writeUInt32LE(entry.length, tableOffset);
		output.writeUInt32LE(entry.offset, tableOffset + 4);
		tableOffset += 8;
	}

	let current = translationTableOffset + count * 8;
	for (const buffer of originals) {
		buffer.copy(output, current);
		current += buffer.length;
	}
	for (const buffer of translated) {
		buffer.copy(output, current);
		current += buffer.length;
	}

	return output;
}

function buildJed(messages) {
	const localeData = {
		'': {
			domain: 'messages',
			lang: locale,
			'plural-forms': 'nplurals=2; plural=(n > 1);',
		},
	};

	for (const [msgid] of messages) {
		localeData[msgid] = [translations[msgid] ?? msgid];
	}

	return `${JSON.stringify({
		'translation-revision-date': new Date().toISOString(),
		generator: 'scripts/i18n-build.mjs',
		domain: 'messages',
		locale_data: {
			messages: localeData,
		},
	}, null, 2)}\n`;
}

fs.mkdirSync(langDir, { recursive: true });
const messages = extractMessages();
const entries = new Map(messages.map(([msgid]) => [msgid, translations[msgid] ?? msgid]));
entries.set('', header('lomi. for WooCommerce', locale));

fs.writeFileSync(path.join(langDir, `${domain}.pot`), buildPot(messages));
fs.writeFileSync(path.join(langDir, `${domain}-${locale}.po`), buildPo(messages));
fs.writeFileSync(path.join(langDir, `${domain}-${locale}.mo`), buildMo(entries));

const jed = buildJed(messages);
for (const handle of scriptHandles) {
	fs.writeFileSync(path.join(langDir, `${domain}-${locale}-${handle}.json`), jed);
}

const untranslated = messages.map(([msgid]) => msgid).filter((msgid) => !(msgid in translations));
console.log(`Generated ${messages.length} messages for ${domain}.`);
console.log(`Untranslated entries using source text: ${untranslated.length}`);
if (untranslated.length > 0) {
	console.log(untranslated.map((msgid) => `- ${msgid}`).join('\n'));
}
