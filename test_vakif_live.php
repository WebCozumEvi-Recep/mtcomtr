<?php
$url = 'https://3dsecure.vakifbank.com.tr/MPIAPI/MPI_Enrollment.aspx';
$params = [
    'MerchantId' => '12345',
    'MerchantPassword' => 'd9QSf04Z',
    'VerifyEnrollmentRequestId' => 'TEST123456',
    'Pan' => '4444444444444444',
    'ExpiryDate' => '2609',
    'PurchaseAmount' => '10.00',
    'Currency' => '949',
    'BrandName' => '100',
    'SuccessUrl' => 'http://example.com/success',
    'FailureUrl' => 'http://example.com/fail',
    'SessionInfo' => 'TEST123456',
    'VerifyType' => '3D_Pay',
    'TerminalNo' => 'VP670049'
];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
echo "Response with MerchantPassword:\n$res\n\n";

$params['Password'] = 'd9QSf04Z';
unset($params['MerchantPassword']);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
echo "Response with Password:\n$res\n\n";
