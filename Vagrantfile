# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/trusty64"

  config.vm.network "private_network", ip: "192.168.26.14"
  config.vm.network "forwarded_port", guest: 22, host: 26014

  config.vm.provider "virtualbox" do |v|
      v.memory = 2048
      v.cpus = 2
    end

  config.vm.provision "shell", path: "vagrant/setup.sh"
end
