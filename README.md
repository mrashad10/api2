# api2

A very simple RESTful API framework, helping you create your own API to serving your project.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

This is a very simple system, using vanilla PHP, you just need to install a few libraries to access the database and validate the inputs using "composer"

### Installing

Just clone the repo then run "composer" installation

```
composer install
```

If you need a database access just add the credential in config file

```
vi libs/config.php
```

Now your code is ready to playing with, you can start writing the code

## Running the tests

Move into tests directory and run this to install the requirements
```
npm install
```

then edit the file 'example.test.js' to set your own URL
then run the test
```
npm run test
```

## Deployment

Simply copy & past the code or use "git archive" to ship the code

## How to use

* Add the endpoint that you need under "endpoints" directory following the "example.php" structure
* Build the function that you need for each verb

* Call that endpoint using any standard REST client

## Built With

* [Medoo](https://medoo.in/) - Lightweight PHP Database Framework
* [Valitron](https://github.com/vlucas/valitron) - Simple, elegant, stand-alone validation library

## Contributing

Please read [CONTRIBUTING.md](https://github.com/mrashad10/api2/blob/master/CONTRIBUTING.md) for details on code of conduct, and the process for submitting pull requests to us.

## ToDo

* Full documentation.

## Versioning

Using [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/mrashad10/api2/tags). 

## Authors

* **Mohamad Rashad** - *Initial work* - [MRashad](https://mrashad.com)

See also the list of [contributors](https://github.com/mrashad10/api2/graphs/contributors) who participated in this project. hope soon you contribute in too

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details

## Acknowledgments

* For [Richard Stallman](https://stallman.org/), [GNU](https://www.gnu.org/) and all free software community
* All YouTubers taught me everything about programming

