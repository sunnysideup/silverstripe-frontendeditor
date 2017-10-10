# How to use

   The aim of this libuary is allow you to easily add a edit page for data in the database.

## Create a dataObject

    This needs to implement FrontEndEditable.

    This is an interface that contains all the methods that you will need to fill out in
    order to create forms for the dataTable

## Adjust config.yml

    You will need to add

    ```
    {DataClassName}:
      extensions:
        - FrontEndDataExtension
    ```

    This will give you more functionality such as being able to see the forms

# Other info

    The Types by default are made from the type of field that is in the database.

    To include a drop down of options for a Model that is not front end editable you need to
    add the name of the relationship to the FrontEndCustomRelationFields methods return value.
