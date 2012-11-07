require 'rubygems'
require 'zmq'

context = ZMQ::Context.new 
worker = context.socket ZMQ::REQ
# We use a string identity for ease here
worker.setsockopt ZMQ::IDENTITY, sprintf("%04X-%04X", rand(10000), rand(10000))
worker.connect 'tcp://localhost:5555'

total = 1
worker.send 'ready'
loop do
  workload =  worker.recv

  # Get workload from router, until finished

  p workload
  p "Processed: #{total} uploads"
  total += 1

  # Do some random work
  sleep((rand(10) + 1) / 10.0)

  worker.send 'free'
end


