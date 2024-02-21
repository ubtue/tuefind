<?php

namespace TueFind\Auth;

class Database extends \VuFind\Auth\Database
{
    public static $countries = ["", "Afghanistan", "Åland Islands", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua And Barbuda", "Argentina", "Armenia", "Aruba", "Ascension Island", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia And Herzegovina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "British Virgin Islands", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burma", "Burundi", "Cambodia", "Cameroon", "Canada", "Canary Islands", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (keeling) Islands", "Colombia", "Comoros", "Congo", "Congo", "Cook Islands", "Costa Rica", "CÔte D'ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Diego Garcia", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "European Union", "Falkland Islands (malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guernsey", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard Island And Mcdonald Islands", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Isle Of Man", "Israel", "Italy", "Jamaica", "Japan", "Jersey", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan", "Lao People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macao", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "North Korea", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Palestinian Territory", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Republic of Macedonia", "RÉunion", "Romania", "Russian Federation", "Rwanda", "Saint Helena", "Saint Kitts And Nevis", "Saint Lucia", "Saint Pierre And Miquelon", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome And Principe", "Saudi Arabia", "Saudi–Iraqi neutral zone", "Senegal", "Serbia", "Serbien und Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia And The South Sandwich Islands", "South Korea", "Soviet Union", "Spain", "Sri Lanka", "Sudan", "Suriname", "Svalbard And Jan Mayen", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "The Gambia", "Togo", "Tokelau", "Tonga", "Trinidad And Tobago", "Tristan da Cunha", "Tunisia", "Turkey", "Turkmenistan", "Turks And Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Viet Nam", "Virgin Islands", "Wallis And Futuna", "Western Sahara", "Yemen", "Zambia", "Zimbabwe"];

    protected function collectParamsFromRequest($request)
    {
        $params = parent::collectParamsFromRequest($request);
        $params['tuefind_institution'] = $request->getPost()->get('tuefind_institution', null);
        $params['tuefind_country'] = $request->getPost()->get('tuefind_country', null);
        return $params;
    }

    protected function createUserFromParams($params, $table)
    {
        $user = parent::createUserFromParams($params, $table);
        $user->setInstitution($params['tuefind_institution']);
        $user->setTuefindCountry($params['tuefind_country']);
        return $user;
    }
}
