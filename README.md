<p align="center">
    <img src="https://raw.githubusercontent.com/jasperweyne/helpless-kiwi/master/assets/image/readme-header.png" alt="helpless-kiwi" style="max-width:100%;">
</p>
<h1 align="center">Helpless Kiwi</h1>
<p align="center">An open source activity manager
<br />
<br />
<a href="https://github.com/jasperweyne/helpless-kiwi/issues/new?assignees=&labels=&template=bug_report.md&title=">Report a bug</a>
·
<a href="https://github.com/jasperweyne/helpless-kiwi/issues/new?assignees=&labels=&template=feature_request.md&title=">Request a feature</a>
<br />
</p>
  
[![sc-gate-shield]][sc-project-url] [![sc-reliability-shield]][sc-project-url] [![sc-maintainability-shield]][sc-project-url] [![sc-coverage-shield]][sc-project-url] [![sc-duplicate-lines-shield]][sc-project-url]

![ci-shield] [![gitpod-shield]][gitpod-url] [![discord-shield]]([discord-url])

- [About Helpless Kiwi](#about-helpless-kiwi)
- [Getting Started](#getting-started)
	- [Deployment](#deployment)
	- [Development](#development)
- [Contributing](#contributing)
- [Partners](#partners)
- [Maintainers](#maintainers)
- [Contact](#contact)
- [License](#license)


## About Helpless Kiwi
Helpless Kiwi is a (self-hosted) activity manager for (student) associations.
It can help you manage your activities and the participation to them. Helpless
Kiwi should give you insight into which activities do well, as well as allow
your members a convenient and central way to view and sign up for them.

## Getting started
Regardless of if you're going to develop or wanting to deploy. Kiwi has some
requirements and dependencies. All of which you can find
[here](PREREQUISITES.md).

### Deployment
To get kiwi running on your own server you can do it manually or use our
install script. Both ways are described in our [installation
manual](DEPLOY.md).

### Development
There are currently three ways of running Kiwi
1. You can [run it using Docker](DOCKER.md)
2. You can [run a LAMP/WAMP stack](DEV.md)
3. You can [run it in Gitpod](https://gitpod.io/#https://github.com/jasperweyne/helpless-kiwi)

## Contributing
Of course, there are some [contributing guidelines](CONTRIBUTING.md) and a
[code of conduct](CODE_OF_CONDUCT.md), which we invite you to check out.

We can always use your help [squashing bugs][bug-list-url] or implementing [new
features][feature-list-url]. [Sonarcloud][sc-project-url] scans and checks the
project for a myriad of other 'issues', as shown below. Our goal is to get
these, as much as possible, resolved. Any help with this is more then
appreciated!


[![sc-vuln-shield]][sc-project-url] [![sc-bugs-shield]][sc-project-url] [![sc-smells-shield]][sc-project-url]

## Partners
<p align="center">
<a href="https://viakunst-utrecht.nl/"><img src="https://raw.githubusercontent.com/jasperweyne/helpless-kiwi/develop/assets/image/readme-viakunst.png" alt="viakunst" height="50px"></a>
<a href="https://particolarte.nl/"><img src="https://raw.githubusercontent.com/jasperweyne/helpless-kiwi/develop/assets/image/readme-particolarte.png" alt="particolarte" height="100px"></a>
</p>

## Maintainers
| **Name**                                           | **Organisation** |
| -------------------------------------------------- | ---------------- |
| [Jasper Weyne](https://github.com/jasperweyne)     | Project Owner    |
| [Eva Biesot](https://github.com/eeeevieb)          | Particolarte     |
| [Karel Zijp](https://github.com/zpks)              | Particolarte     |
| [Peter Sabel (Zabel)](https://github.com/A-Daneel) | Particolarte     |
| Arnold van Bemmelen                                | ViaKunst         |
| [David Koymans](https://github.com/DavidckPixel)   | ViaKunst         |
| [Machiel Kruger](https://github.com/mkrugr)        | ViaKunst         |

## Contact
You can either join the [discord](https://discord.gg/4HUmvEnXn8), file a [bug
report][bug-create-url], or make a [feature request][feature-create-url].

## License
This work is [licensed](https://github.com/jasperweyne/helpless-kiwi/blob/develop/LICENSE) under the [Apache License 2.0](https://www.apache.org/licenses/LICENSE-2.0).

[sc-project-url]: https://sonarcloud.io/dashboard?id=jasperweyne_helpless-kiwi
[sc-gate-shield]: https://sonarcloud.io/api/project_badges/measure?project=jasperweyne_helpless-kiwi&metric=alert_status
[sc-reliability-shield]: https://sonarcloud.io/api/project_badges/measure?project=jasperweyne_helpless-kiwi&metric=reliability_rating
[sc-maintainability-shield]: https://sonarcloud.io/api/project_badges/measure?project=jasperweyne_helpless-kiwi&metric=sqale_rating
[sc-coverage-shield]: https://sonarcloud.io/api/project_badges/measure?project=jasperweyne_helpless-kiwi&metric=coverage
[sc-duplicate-lines-shield]: https://sonarcloud.io/api/project_badges/measure?project=jasperweyne_helpless-kiwi&metric=duplicated_lines_density
[sc-bugs-shield]: https://sonarcloud.io/api/project_badges/measure?project=jasperweyne_helpless-kiwi&metric=bugs
[sc-smells-shield]: https://sonarcloud.io/api/project_badges/measure?project=jasperweyne_helpless-kiwi&metric=code_smells
[sc-vuln-shield]: https://sonarcloud.io/api/project_badges/measure?project=jasperweyne_helpless-kiwi&metric=vulnerabilities

[gitpod-url]: https://gitpod.io/#https://github.com/jasperweyne/helpless-kiwi
[gitpod-shield]: https://img.shields.io/badge/Gitpod-ready--to--code-success?logo=gitpod

[discord-url]: https://discord.gg/4HUmvEnXn8
[discord-shield]: https://img.shields.io/discord/838843751509393458?label=discord&logo=discord

[ci-shield]: https://github.com/jasperweyne/helpless-kiwi/workflows/CI/badge.svg?branch=develop


[bug-list-url]: https://github.com/jasperweyne/helpless-kiwi/issues?q=is%3Aissue+is%3Aopen+label%3A%22type%3A+bug%22
[bug-create-url]: https://github.com/jasperweyne/helpless-kiwi/issues/new?assignees=&labels=&template=bug_report.md&title=
[feature-list-url]: https://github.com/jasperweyne/helpless-kiwi/issues?q=is%3Aissue+is%3Aopen+label%3A%22type%3A+feature%22
[feature-create-url]: https://github.com/jasperweyne/helpless-kiwi/issues/new?assignees=&labels=&template=feature_request.md&title=

