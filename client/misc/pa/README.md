#CSVs
Use `tests/fixtures/behat-pa.csv`

#Useful commands


    # query to activate digideps+pa1@digital.justice.gov.uk user with 
    # same password as laydeputy@publicguardian.gov.uk user
    update dd_user set active=true, password = (select password from dd_user where email = 'laydeputy@publicguardian.gov.uk') WHERE email='digideps+pa1@digital.justice.gov.uk';