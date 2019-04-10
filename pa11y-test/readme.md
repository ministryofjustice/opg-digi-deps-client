# Pa11y test for accessibility

Note: This is to be done outside of containers, on your system.

Package details: https://github.com/pa11y/pa11y

## Steps to setup

1. Ensure you have Node version 8+ (node -v), or install it:

2. LINUX: https://linuxize.com/post/how-to-install-node-js-on-ubuntu-18.04/

3. MAC: https://blog.teamtreehouse.com/install-node-js-npm-mac

4. Or you can use nvm and switch between versions if needed

5. npm install phantomjs-prebuilt -g

6. npm install (In /pa11y-test folder)

7. Run the following command: `node pa11y h [host excluding slash] r [route including slash in front] e [user email] p [pass]`

Example: `node pa11y -h digideps-client.local -r /report/3/overview -e abc@def.com -p Abcd1234`

You can view the title in the output to know if you’ve hit the right page or not.
