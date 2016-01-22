require(["Orders/EmailInvoice"], function(EmailInvoice)
{
    var emailInvoice = new EmailInvoice();
    emailInvoice.init('<?=$selector;?>');
});