<?php
namespace Core\Classes;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer();
        $this->configure();
    }
    
    private function configure() {
        // Configuration SMTP
        $this->mail->isSMTP();
        $this->mail->Host = "192.168.0.247";  // Serveur SMTP
        $this->mail->SMTPAuth = true;
        $this->mail->Username = "fabrice.gousse@info.groupesorepco.com";
        $this->mail->Password = "dirSRPC2854";
        $this->mail->Port = 587;
        
        // Désactiver la vérification SSL (pour serveur local)
        $this->mail->smtpConnect([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true
            ]
        ]);
        
        // Expéditeur par défaut
        $this->mail->setFrom("arlette.njinkeu@info.groupesorepco.com", "Service Comptabilité SOREPCO");
        $this->mail->isHTML(true);
    }
    
    /**
     * Envoie une notification de validation d'opération
     * 
     * @param array $operation Les données de l'opération
     * @param string $emailDestinataire L'email du chef d'agence
     * @return bool Succès ou échec
     */
    public function sendValidationNotification($operation, $emailDestinataire) {
        try {
            // Nettoyer l'objet mail pour un nouvel envoi
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            
            // Déterminer le type et le numéro
            $typeTexte = ($operation['type_operation'] === 'cheque') ? 'chèque' : 'virement';
            $reference = ($operation['type_operation'] === 'cheque') 
                ? 'n° ' . $operation['numero_cheque'] 
                : 'référence ' . $operation['numero_cheque'];
            
            // Sujet de l'email
            $this->mail->Subject = "Confirmation et validation de " . $typeTexte . " " . $reference;
            
            // Destinataire
            $this->mail->addAddress($emailDestinataire);
            
            // Corps du message en HTML
            $body = $this->buildEmailBody($typeTexte, $reference, $operation);
            $this->mail->Body = $body;
            
            // Version texte brut (pour les clients email qui n'affichent pas HTML)
            $this->mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $body));
            
            // Envoyer
            return $this->mail->send();
            
        } catch (Exception $e) {
            // Journaliser l'erreur
            error_log("Erreur PHPMailer: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Construit le corps de l'email en HTML
     */
    private function buildEmailBody($typeTexte, $reference, $operation) {
        $date = date('d/m/Y H:i');
        $montant = number_format($operation['montant'], 0, ',', ' ');
        $client = htmlspecialchars($operation['nom_client']);
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
                .header { background: linear-gradient(135deg, #0047ab 0%, #1a5fc9 100%); color: white; padding: 20px; text-align: center; }
                .header h2 { margin: 0; font-size: 24px; }
                .header p { margin: 5px 0 0; opacity: 0.9; }
                .content { padding: 30px; background: #f9f9f9; }
                .info-box { background: white; border-left: 4px solid #0047ab; padding: 15px; margin: 20px 0; border-radius: 3px; }
                .info-box p { margin: 5px 0; }
                .label { font-weight: bold; color: #0047ab; }
                .footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; }
                .signature { margin-top: 25px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>SOREPCO</h2>
                    <p>Service Comptabilité</p>
                </div>
                
                <div class='content'>
                    <p>Bonjour,</p>
                    
                    <p>Nous vous informons que le <strong>$typeTexte $reference</strong> a été <strong style='color: #28a745;'>confirmé et validé</strong> par le service comptabilité de SOREPCO.</p>
                    
                    <div class='info-box'>
                        <p><span class='label'>Client :</span> $client</p>
                        <p><span class='label'>Montant :</span> $montant FCFA</p>
                        <p><span class='label'>Banque :</span> " . htmlspecialchars($operation['banque']) . "</p>
                        <p><span class='label'>Date de validation :</span> $date</p>
                    </div>
                    
                    <p>Vous pouvez donc considérer ce paiement comme <strong>effectif</strong>.</p>
                    
                    <p>Restant à votre disposition pour toute information complémentaire.</p>
                    
                    <div class='signature'>
                        <p>Cordialement,<br>
                        <strong>Service Comptabilité</strong><br>
                        Groupe SOREPCO</p>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>Ce message est automatique, merci de ne pas y répondre.</p>
                    <p>© " . date('Y') . " Groupe SOREPCO - Tous droits réservés</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}