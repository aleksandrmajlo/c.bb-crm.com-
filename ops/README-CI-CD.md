# c.bb-crm.com CI/CD

## ELI5

GitHub Actions builds the frontend, puts the application into a package and
uploads it to a staging directory. The restricted `deploy-cbbcrm` user can
upload that package but cannot edit the live website directly. A fixed,
root-owned deployment script validates the package, installs locked Composer
dependencies, publishes the code and checks `/utc`.

The workflow never runs database migrations.

## Server installation

Run these commands locally from this repository:

```bash
scp -P 22 ops/deploy-cbbcrm ops/deploy-cbbcrm.sudoers root@5.9.194.218:/tmp/
```

Then connect as root:

```bash
ssh root@5.9.194.218 -p 22
```

Protect the project deployment root while keeping the incoming directory
writable by the deployment user:

```bash
chown root:root /srv/deploy/c.bb-crm.com
chmod 755 /srv/deploy/c.bb-crm.com
chown deploy-cbbcrm:deploy-cbbcrm /srv/deploy/c.bb-crm.com/incoming
chmod 750 /srv/deploy/c.bb-crm.com/incoming
```

Install and validate the fixed deployment command:

```bash
install -o root -g root -m 750 /tmp/deploy-cbbcrm /usr/local/sbin/deploy-cbbcrm
install -o root -g root -m 440 /tmp/deploy-cbbcrm.sudoers /etc/sudoers.d/deploy-cbbcrm
visudo -cf /etc/sudoers.d/deploy-cbbcrm
bash -n /usr/local/sbin/deploy-cbbcrm
rm -f /tmp/deploy-cbbcrm /tmp/deploy-cbbcrm.sudoers
```

The expected `visudo` result is:

```text
/etc/sudoers.d/deploy-cbbcrm: parsed OK
```

Confirm that only the fixed command is allowed:

```bash
sudo -l -U deploy-cbbcrm
```

## GitHub production environment

Create an environment named `production` and restrict it to the `main`
branch. Add these environment secrets:

- `DEPLOY_SSH_KEY`: contents of `~/.ssh/c_bb_crm_github_actions`
- `DEPLOY_KNOWN_HOSTS`: verified `ssh-keyscan` output for the server

Add these environment variables:

- `DEPLOY_HOST=5.9.194.218`
- `DEPLOY_PORT=22`
- `DEPLOY_USER=deploy-cbbcrm`

## Workflow behavior

For pull requests and pushes, CI runs:

```bash
npm ci
npm run build
```

Because `public/build` is intentionally tracked for now, CI also checks that
the freshly generated build is committed. A push to `main` deploys only after
the build passes and the `production` environment permits it.

The server script runs Composer with production flags, preserves `.env`,
`storage`, `bootstrap/cache` and `public/storage`, and never runs
`php artisan migrate`.
