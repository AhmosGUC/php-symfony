DONE:
- Handle CSV file Upload with a form
- import CSV
- Calcualte deadline according to order type
- Calcualte deadline according to state
- Reschdule deadline if on weekend or holiday
- Handle errors in csv file

TO-DO:
- Save import to database
- Make each functionality on different page
- Handle duplicate projects


Routes:
- '/' for the DeafualtController

Controllers:
- DefaultController

DefaultController Functions:
- avoidDayOff($deadline):string recalculates deadline if on holiday
- calcDeadline($state,$startDate,$commencementDate,$ordType):string calculates deadline according to variable
- parseLine($data,$row):void reads the data in a csv line
- newAction(Request $request) handles user file upload and submission


RUN:
- composer install
- symfony server:start

