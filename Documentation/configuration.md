# Configuration

## :house: Access

To access to Pagantis admin panel, we need to open the prestashop admin panel and follow the next steps:

1. Click on : Modules and Services => Modules and Services
![Step 1](./prestashop_installation_1.png?raw=true "Step 1")

2. Type Pagantis in the search field then click on  Configure
![Step 2](./prestashop_configuration_2.png?raw=true "Step 2")

3. Pagantis
![Step 3](./prestashop_configuration_3.png?raw=true "Step 3")

## :clipboard: Options
In the Pagantis admin panel, we can set the following options:

| Field &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;| Description<br/><br/>
| :------------- |:-------------| 
| Module is enabled    | * Yes => Activates the payment method (Default) <br/> * No => Disables the payment method
| Public Key(*) |  String.
| Secret Key(*) |  String. 
| Simulator is enabled |  * Yes => Display the installments simulator  (Default) <br/> * No => Do not display the simulator

:information_source: - Your keys are located on your [Pagantis profile](https://bo.pagantis.com/shop)


## :clipboard: Advanced configuration:
While we recommend using the Pagantis module as is , you can customize some settings as shown below.

You have to ways to edit your settings:
* [Database queries](./configuration.md#edit-your-settings-using-database-queries)
* [HTTP requests](./configuration.md#edit-your-settings-using-postman)

##### List of settings and their description.

> __Static__ values cannot be edited.

| Field | Description<br/><br/>
| :------------- |:-------------| 
| PAGANTIS_TITLE                           | Payment title to show in checkout page. By default:"Instant financing".
| PAGANTIS_SIMULATOR_DISPLAY_TYPE          | Installments simulator skin inside product page, in positive case. Recommended value: 'pgSDK.simulator.types.SIMPLE'.
| PAGANTIS_SIMULATOR_DISPLAY_SKIN          | Skin of the product page simulator. Recommended value: 'pgSDK.simulator.skins.BLUE'.
| PAGANTIS_SIMULATOR_START_INSTALLMENTS    | Number of installments by default to use in simulator.
| PAGANTIS_SIMULATOR_DISPLAY_CSS_POSITION  | he position where the simulator widget will be injected. Recommended value: 'pgSDK.simulator.positions.INNER'.
| PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR    | CSS selector with DOM element having totalAmount value.
| PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR | CSS Selector to inject the widget. (Example: '#simulator', '.pagantisSimulator')
| PAGANTIS_SIMULATOR_CSS_QUANTITY_SELECTOR | CSS selector with DOM element having the quantity selector value.
| PAGANTIS_FORM_DISPLAY_TYPE               | Allow you to select the way to show the payment form in your site
| PAGANTIS_DISPLAY_MIN_AMOUNT              | Minimum amount to use the module and show the payment method in the checkout page and in product page.
| PAGANTIS_DISPLAY_MAX_AMOUNT              | Maximum amount to use the module and show the payment method in the checkout page and in product page.
| PAGANTIS_URL_OK                          | Location where user will be redirected after a successful payment. This string will be concatenated to the base url to build the full url
| PAGANTIS_URL_KO                          | Location where user will be redirected after a wrong payment. This string will be concatenated to the base url to build the full url  
| PAGANTIS_ALLOWED_COUNTRIES               | Array of country codes where the method can be used 

##### Edit your settings using database queries
1 - Open your database management (Commonly Cpanel->phpmyadmin depending on your hosting solution) 

2 - Connect to prestashop database. 

3 - Launch a query to check if the table exists:
  * Query: 
        ```
        SELECT * FROM pagantis_config;
        ```
   * Result:
    ![Step 3](./sql_step3.png?raw=true "Step 1")

4 - Find the setting PAGANTIS_TITLE, in this example we are going to change 'Instant Financing' to 'New Title'  

5 - Launch the following query to edit the value:
  * Query: 
        ```
        UPDATE pagantis_config set value='New title' WHERE config='PAGANTIS_TITLE';
        ```
   * Result: 
   ![Step 5](./sql_step5.png?raw=true "Step 5")


6 - After the modification, you can verify it with the following query :
  * Query:
        ```
        SELECT * FROM pagantis_config;
        ```
   * Result: ![Step 6](./sql_step6.png?raw=true "Step 6")

7 - Finally you can see the result on checkout page

![Step 7](./sql_step7_.png?raw=true "Step 7")


##### Edit your settings using Postman

1. Open the application
![Step 1](./postman_step1.png?raw=true "Step 1")

2. Set the mode of the request  
2.1 - Click on BODY tag  
2.2 - Click on x-www-form-urlencoded
![Step 2](./postman_step2.png?raw=true "Step 2")

3. Set your request  
3.1 - On the upper-left side, you need to set a POST request  
3.2 - Fill the url field with your domain, and your secret key which is located on your [Pagantis profile](https://bo.pagantis.com/shop).     
3.3 - Set the config key to modify.[List of config keys](./configuration.md#list-of-settings-and-their-description).  
3.4 - Set the value for the selected key  

![Step 3](./postman_step3.png?raw=true "Step 3")

4. Press SEND
![Step 4](./postman_step4.png?raw=true "Step 4")

5. If everything works correctly, you should see the edited config as show below 
![Step 5](./postman_step5.png?raw=true "Step 5")

6. Finally you can see the result on checkout page

![Step 6](./postman_step6.png?raw=true "Step 6")