<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $wrapperStart = '<div style="font-family: \'Outfit\', \'Inter\', sans-serif; background-color: #f7f9fa; padding: 30px 15px; margin: 0; width: 100%;"><div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-top: 5px solid #00bde0;"><div style="background-color: #ffffff; padding: 25px; text-align: center; border-bottom: 1px solid #edf2f7;"><h1 style="color: #1a202c; font-size: 22px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">PRAMUKH SANKALP</h1><p style="color: #718096; font-size: 13px; margin: 5px 0 0 0; letter-spacing: 1px;">PRAMUKH ACADEMY</p></div><div style="padding: 30px 25px; color: #2d3748; line-height: 1.6; font-size: 15px;">';
        $wrapperEnd = '</div></div></div>';

        $cta = function (string $label) {
            return '<div style="text-align: center; margin: 25px 0;"><a href="{{payment_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">' . $label . '</a></div>'
                . '<p style="font-size: 13px; color: #718096; text-align: center;">Or copy and paste this link into your web browser:<br><a href="{{payment_link}}" style="color: #00bde0; text-decoration: none;">{{payment_link}}</a></p>';
        };

        $note = '<p style="background-color: #f0fbff; border-left: 4px solid #00bde0; padding: 12px 16px; border-radius: 6px; font-size: 14px; color: #2d3748;">{{next_batch_note}}</p>';

        $template = [
            'identifier' => 'student_lead_payment_link',

            'title_en' => 'Registrations Are Open – Complete Your Payment for PRAMUKH SANKALP',
            'content_en' => $wrapperStart
                . '<p>Dear SANKALP Guardian <strong>{{guardian_name}}</strong>,</p>'
                . '<p>Good news! Registration for the new batch of the Pramukh SANKALP Programme is now open.</p>'
                . '<p>We already have <strong>{{student_name}}</strong>\'s application details on record, so there is <strong>no need to fill the form again</strong>. Just click the button below to choose a plan and complete the payment.</p>'
                . $cta('Choose Plan &amp; Pay')
                . $note
                . '<p style="font-size: 13px; color: #718096;">This link is specific to your application. If you did not submit this application, please ignore this email.</p>'
                . '<p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">Warm regards,<br><strong>Pramukh SANKALP Team</strong><br>Pramukh Academy</p>'
                . $wrapperEnd,

            'title_hi' => 'पंजीकरण शुरू – प्रमुख संकल्प के लिए अपना भुगतान पूरा करें',
            'content_hi' => $wrapperStart
                . '<p>प्रिय संकल्प संरक्षक <strong>{{guardian_name}}</strong>,</p>'
                . '<p>शुभ समाचार! प्रमुख संकल्प कार्यक्रम के नए बैच के लिए पंजीकरण अब आरंभ हो चुका है।</p>'
                . '<p><strong>{{student_name}}</strong> के आवेदन का विवरण हमारे पास पहले से सुरक्षित है, इसलिए आपको <strong>फ़ॉर्म दोबारा भरने की आवश्यकता नहीं है</strong>। नीचे दिए गए बटन पर क्लिक करके योजना चुनें और भुगतान पूरा करें।</p>'
                . $cta('योजना चुनें और भुगतान करें')
                . $note
                . '<p style="font-size: 13px; color: #718096;">यह लिंक केवल आपके आवेदन के लिए है। यदि आपने यह आवेदन नहीं किया है, तो कृपया इस ईमेल को अनदेखा करें।</p>'
                . '<p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">सादर,<br><strong>प्रमुख संकल्प टीम</strong><br>प्रमुख अकादमी</p>'
                . $wrapperEnd,

            'title_gu' => 'નોંધણી શરૂ – પ્રમુખ સંકલ્પ માટે તમારી ચુકવણી પૂર્ણ કરો',
            'content_gu' => $wrapperStart
                . '<p>પ્રિય સંકલ્પ વાલી <strong>{{guardian_name}}</strong>,</p>'
                . '<p>ખુશખબર! પ્રમુખ સંકલ્પ કાર્યક્રમની નવી બેચ માટે નોંધણી હવે શરૂ થઈ ગઈ છે.</p>'
                . '<p><strong>{{student_name}}</strong> ની અરજીની વિગતો અમારી પાસે પહેલેથી જ સચવાયેલી છે, તેથી <strong>ફોર્મ ફરીથી ભરવાની જરૂર નથી</strong>. નીચેના બટન પર ક્લિક કરીને પ્લાન પસંદ કરો અને ચુકવણી પૂર્ણ કરો.</p>'
                . $cta('પ્લાન પસંદ કરો અને ચૂકવો')
                . $note
                . '<p style="font-size: 13px; color: #718096;">આ લિંક ફક્ત તમારી અરજી માટે છે. જો તમે આ અરજી કરી ન હોય, તો કૃપા કરીને આ ઈમેલ અવગણો.</p>'
                . '<p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">સાદર,<br><strong>પ્રમુખ સંકલ્પ ટીમ</strong><br>પ્રમુખ એકેડમી</p>'
                . $wrapperEnd,

            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Do not clobber admin edits if the template already exists.
        $exists = DB::table('mail_templates')->where('identifier', $template['identifier'])->exists();

        DB::table('mail_templates')->updateOrInsert(
            ['identifier' => $template['identifier']],
            $exists ? ['updated_at' => now()] : $template
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('mail_templates')->where('identifier', 'student_lead_payment_link')->delete();
    }
};
