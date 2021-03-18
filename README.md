# GlitcherBot Google Sheets Subscriber

Listens for glitcherbot crawling events, gathers data, and will write data to Google sheets at the end of the crawl.

The sheet is written to Google Drive in the root folder, and has the title 'GlitcherBot Data' (unless you specify an 
existing sheet ID - see below).

## Configuration

You will need to create a new application via the Google developer's console and create some OAUTH2 credentials with 
permissions to write to Google sheets.  The type should be 'Desktop App'.

Once done, download your credentials.json file and place it in the GoogleSheets folder.

On first run, you will be given a URL to authenticate against. Open this in a browser. You'll receive a code that 
you'll need to copy and paste into the console window.  Once authorised, a refresh token is stored in `token.json` in 
the `GoogleSheets` folder. You may be required to repeat this process from time to time.

### Re-using an existing sheet

To append data to an existing sheet, create a `config.yaml` file in the `GoogleSheets` folder and set the value of 
`sheet_id` to the ID of the sheet you want to use.  See `example.config.yaml` for the format.

If `config.yaml` does not exist, on the first run, a sheet will be created and the `config.yaml` file will be created 
and populated so that the same sheet is used on subsequent runs.
