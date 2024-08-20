<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require 'vendor/autoload.php';

use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use League\OAuth2\Client\Token\AccessToken;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;

$subdomain = 'shvartsenberg2015.amocrm.ru';
$clientId = '0cd7be3f-4461-47cd-abd5-658fb94a9eab';
$clientSecret = 'EAINlHGfJZmnmJFef96PkDLvG4Sn4PUewFwsmzGgoeOrLT04rIqkqG7EPB0v9B03';
$redirectUri = 'shvartsenberg.ru/create_deal.php';

$apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
$accessTokenString = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImVjZjFiYTkxZWRhNzM5OGJmOGUwOGI2OTQyZDk0MGU3YTNkNGNmNDZjOGNjZGRkZWUzM2RlMzFlNmQxOTEzN2NjZTcwZmQ2YTQwZTUxZDZiIn0.eyJhdWQiOiIwY2Q3YmUzZi00NDYxLTQ3Y2QtYWJkNS02NThmYjk0YTllYWIiLCJqdGkiOiJlY2YxYmE5MWVkYTczOThiZjhlMDhiNjk0MmQ5NDBlN2EzZDRjZjQ2YzhjY2RkZGVlMzNkZTMxZTZkMTkxMzdjY2U3MGZkNmE0MGU1MWQ2YiIsImlhdCI6MTcyNDA2MTk4OSwibmJmIjoxNzI0MDYxOTg5LCJleHAiOjE3Mjc2NTQ0MDAsInN1YiI6IjExNDA4ODA2IiwiZ3JhbnRfdHlwZSI6IiIsImFjY291bnRfaWQiOjMxOTAzMzg2LCJiYXNlX2RvbWFpbiI6ImFtb2NybS5ydSIsInZlcnNpb24iOjIsInNjb3BlcyI6WyJjcm0iLCJmaWxlcyIsImZpbGVzX2RlbGV0ZSIsIm5vdGlmaWNhdGlvbnMiLCJwdXNoX25vdGlmaWNhdGlvbnMiXSwiaGFzaF91dWlkIjoiMWRiMTM3MTAtZTNiMi00ZGYzLWEzNjktNGE3N2IwN2RmNjE4In0.UKVt50PIqYeuV7pbWvCEpcf_cWLI8uuHAJADba0R5B8nm3mpD4RHKsCaKIAm-1q1omdjE2Si7WGkMuTkT3wbu8Vq21ry6G-6pCemxyhmRi3OKUWig_SRwTqQXAbjlh6a-Cd-J7mGTN74oKBsNIFlN_2B_FDmw8wchHChE5z67wgLjowH07ktnHnA8EG3hLOJuY1tN-0ONbkHfDOL_ERqvEd768mYzVVBKESN78O5UNMEYqN-5yWm4iDXQEwO_cICUQQeEQo691ze4Xyivqwl7sV-_x8obBjVDTmOzh6WSKk9rBeDou6c0y8ncDXrs8BK-SfNtJ6OLb008JFUEqShZg';
$accessToken = new AccessToken(['access_token' => $accessTokenString, 'expires' => time() + 100000000]);

$apiClient->setAccessToken($accessToken)->setAccountBaseDomain($subdomain);

if (!isset($_SESSION['start_time'])) {
    $_SESSION['start_time'] = time();
}

$userTimeOnSite = (time() - $_SESSION['start_time'] >= 30) ? 1 : 0;

$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$price = $_POST['price'];

$externalData = [
    [
        'is_new' => true,
        'price' => $price,
        'name' => "Сделка с сайта shvartsenberg.ru от $name",
        'contact' => [
            'first_name' => $name,
            'last_name' => null,
            'phone' => $phone,
            'email' => $email
        ]
    ]
];

$leadsCollection = new LeadsCollection();
foreach ($externalData as $externalLead) {
    $lead = (new LeadModel())
        ->setName($externalLead['name'])
        ->setPrice($externalLead['price'])
        ->setContacts(
            (new ContactsCollection())
                ->add(
                    (new ContactModel())
                        ->setFirstName($externalLead['contact']['first_name'])
                        ->setLastName($externalLead['contact']['last_name'])
                        ->setCustomFieldsValues(
                            (new CustomFieldsValuesCollection())
                                ->add(
                                    (new MultitextCustomFieldValuesModel())
                                        ->setFieldCode('PHONE')
                                        ->setValues(
                                            (new MultitextCustomFieldValueCollection(
                                            ))
                                                ->add(
                                                    (new MultitextCustomFieldValueModel(
                                                    ))
                                                        ->setValue(
                                                            $externalLead['contact']['phone']
                                                        )
                                                )
                                        )
                                )
                                ->add(
                                    (new MultitextCustomFieldValuesModel())
                                        ->setFieldCode('EMAIL')
                                        ->setValues(
                                            (new MultitextCustomFieldValueCollection(
                                            ))
                                                ->add(
                                                    (new MultitextCustomFieldValueModel(
                                                    ))
                                                        ->setValue(
                                                            $externalLead['contact']['email']
                                                        )
                                                )
                                        )
                                )
                                ->add(
                                    (new MultitextCustomFieldValuesModel())
                                        ->setFieldId(1054611)
                                        ->setValues(
                                            (new NumericCustomFieldValueCollection(
                                            ))
                                                ->add(
                                                    (new NumericCustomFieldValueModel(
                                                    ))
                                                        ->setValue(
                                                            $userTimeOnSite
                                                        )
                                                )
                                        )
                                )
                        )
                )
        );
    $leadsCollection->add($lead);
}
try {
    $addedLeadsCollection = $apiClient->leads()->addComplex($leadsCollection);
    header('Location: success.php');
    exit();
} catch (AmoCRMApiException $e) {
    echo '<pre>';
    print_r($e);
    echo '</pre>';
    die;
}