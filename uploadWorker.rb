require 'rubygems'
require 'zmq'
require 'net/http'
require 'uri'
require 'json'

context = ZMQ::Context.new
fe_tasks = context.socket ZMQ::REQ
# We use a string identity for ease here
fe_tasks.setsockopt ZMQ::IDENTITY, sprintf("%04X-%04X", rand(10000), rand(10000))
fe_tasks.connect 'tcp://localhost:5555'

total = 1
fe_tasks.send 'ready'
loop do
  workload = fe_tasks.recv
  if workload =='added'
    p "registered"
  else
    #p workload
    #sleep((rand(10) + 1) / 10.0)

    p "Processed: #{total} uploads"
    total += 1

    # Do some random work
    data = JSON.parse workload
    data[:key]="wohdfo97wg4iurvfdc t7yaigvrufbs"
    r = Net::HTTP.post_form URI("http://ta-d/savedata"), data
    if r.body=="ok"
      p "ok"
    else
      p r.code
      p r.body
    end
  end
  fe_tasks.send 'free'
end


